<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmation;
use App\Mail\OrderShipped;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\Invoices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

class InvoicesTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config([
            'shop.vat.rate' => 21,
            'shop.business' => ['name' => 'Rukn', 'address' => 'Damrak 1, 1012 LG Amsterdam', 'kvk' => '12345678', 'vat_id' => 'NL001234567B01', 'iban' => null],
            'shop.contact_email' => 'info@rukn.nl',
        ]);
        $this->product = Product::factory()->for(Category::factory())->create(['name' => 'Black Abaya', 'price' => 121, 'stock' => 10, 'is_active' => true]);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function order(array $attributes = []): Order
    {
        $order = Order::create($attributes + [
            'status' => 'pending', 'payment_status' => 'open', 'payment_method' => 'cod', 'locale' => 'nl',
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam', 'country' => 'NL',
            'subtotal' => 121, 'shipping_cost' => 0, 'total' => 121, 'vat_rate' => 21, 'vat_amount' => 21,
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $this->product->id, 'product_name' => 'Black Abaya', 'variant_label' => '54 · Zwart', 'quantity' => 1, 'price' => 121]);

        return $order;
    }

    private function ship(Order $order, string $status = 'shipped')
    {
        return $this->actingAs($this->admin)->patch("/en/admin/orders/{$order->id}", ['status' => $status]);
    }

    // ---- Numbering ----

    public function test_numbers_are_sequential_per_year_and_assigned_on_shipping(): void
    {
        $a = $this->order();
        $b = $this->order();
        $this->assertNull(Invoices::assign($a));   // pay on delivery, not shipped: no invoice yet

        $this->ship($b);
        $this->ship($a);

        $year = now()->format('Y');
        $this->assertSame("{$year}-0001", $b->fresh()->invoice_number);
        $this->assertSame("{$year}-0002", $a->fresh()->invoice_number);
        $this->assertNotNull($a->fresh()->invoiced_at);
    }

    public function test_a_number_is_given_once_and_never_to_cancelled_orders(): void
    {
        $order = $this->order();
        $cancelled = $this->order(['status' => 'cancelled']);

        $this->ship($order);
        $this->ship($order, 'delivered');
        Invoices::assign($order->fresh());
        Invoices::assign($cancelled);

        $this->assertSame(1, Order::whereNotNull('invoice_number')->count());
        $this->assertNull($cancelled->fresh()->invoice_number);
    }

    public function test_paid_online_orders_get_a_number_without_shipping(): void
    {
        $order = $this->order(['payment_method' => 'mollie', 'payment_status' => 'paid', 'paid_at' => now()]);

        $this->assertNotNull(Invoices::assign($order));
    }

    public function test_the_sequence_restarts_each_year(): void
    {
        $this->travelTo(now()->setDate(2026, 12, 31));
        Invoices::assign($this->order(['status' => 'shipped']));
        $this->travelTo(now()->setDate(2027, 1, 1));
        $order = $this->order(['status' => 'shipped']);
        Invoices::assign($order);

        $this->assertSame('2027-0001', $order->fresh()->invoice_number);
    }

    public function test_older_orders_without_stored_vat_get_it_when_invoiced(): void
    {
        $order = $this->order(['vat_rate' => null, 'vat_amount' => null, 'status' => 'shipped']);

        Invoices::assign($order);

        $this->assertSame('21.00', $order->fresh()->vat_rate);
        $this->assertSame('21.00', $order->fresh()->vat_amount);
    }

    // ---- PDF ----

    public function test_the_pdf_is_a_real_pdf_with_the_required_details(): void
    {
        $order = $this->order(['status' => 'shipped']);
        Invoices::assign($order);

        $pdf = Invoices::pdf($order->fresh());
        $this->assertStringStartsWith('%PDF-', $pdf);

        // The HTML that becomes the PDF (text inside the PDF is compressed).
        app()->setLocale('nl');
        $html = view('invoices.pdf', ['order' => $order->fresh()->load('items'), 'business' => config('shop.business')])->render();
        foreach (['Factuur', $order->fresh()->invoice_number, 'Damrak 1, 1012 LG Amsterdam', '12345678', 'NL001234567B01',
                  'Black Abaya (54 · Zwart)', 'Totaal excl. btw', '€ 100,00', 'Btw 21%', '€ 21,00', '€ 121,00', 'Betaling bij aflevering'] as $text) {
            $this->assertStringContainsString($text, $html, $text);
        }
    }

    public function test_english_for_non_dutch_orders(): void
    {
        $this->assertSame('en', Invoices::locale(new Order(['locale' => 'ar'])));
        $this->assertSame('en', Invoices::locale(new Order(['locale' => 'en'])));
        $this->assertSame('nl', Invoices::locale(new Order(['locale' => 'nl'])));
    }

    // ---- Access ----

    public function test_customer_downloads_with_the_private_link_only(): void
    {
        $order = $this->order(['status' => 'shipped']);
        Invoices::assign($order);
        $order->refresh();

        $this->get($order->invoiceUrl('en'))->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="Rukn-factuur-' . $order->invoice_number . '.pdf"');

        $this->get("/en/orders/invoice/{$order->id}/" . str_repeat('x', 40))->assertNotFound();
        $this->get($order->privateUrl('en'))->assertSee('Download invoice ' . $order->invoice_number);
    }

    public function test_no_invoice_before_it_is_due(): void
    {
        $order = $this->order();

        $this->get($order->invoiceUrl('en'))->assertNotFound();
        $this->actingAs($this->admin)->get("/en/admin/orders/{$order->id}/invoice")->assertNotFound();
        $this->get($order->privateUrl('en'))->assertDontSee('Download invoice');
    }

    public function test_admin_download_and_missing_details_warning(): void
    {
        $order = $this->order(['status' => 'shipped']);
        Invoices::assign($order);

        $this->actingAs($this->admin)->get("/en/admin/orders/{$order->id}/invoice")->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->actingAs($this->admin)->get("/en/admin/orders/{$order->id}")->assertDontSee('Invoices are missing');

        config(['shop.business.vat_id' => null]);
        $this->actingAs($this->admin)->get("/en/admin/orders/{$order->id}")->assertSee('Invoices are missing')->assertSee('SHOP_BUSINESS_VAT_ID');

        $this->actingAs(User::factory()->create())->get("/en/admin/orders/{$order->id}/invoice")->assertForbidden();
    }

    // ---- Emails ----

    public function test_pay_on_delivery_invoice_goes_with_the_shipping_email(): void
    {
        $order = $this->order();

        $this->ship($order);

        Mail::assertQueued(OrderShipped::class, function (OrderShipped $mail) use ($order) {
            // Each PDF carries its own creation time, so compare name, type and content start.
            [$attachment] = $mail->attachments();
            $this->assertSame(Invoices::filename($order->fresh()), $attachment->as);
            $this->assertSame('application/pdf', $attachment->mime);
            $attachment->attachWith(fn () => null, function ($data) { $this->assertStringStartsWith('%PDF-', $data()); });
            $mail->assertSeeInHtml('factuur ' . $order->fresh()->invoice_number);

            return true;
        });
    }

    public function test_confirmation_without_invoice_has_no_attachment(): void
    {
        $order = $this->order();

        $this->assertSame([], (new OrderConfirmation($order))->attachments());
    }

    public function test_online_payment_attaches_the_invoice_to_the_confirmation(): void
    {
        $order = $this->order(['payment_method' => 'mollie', 'payment_status' => 'paid', 'paid_at' => now()]);
        Invoices::assign($order);

        $this->assertCount(1, (new OrderConfirmation($order->fresh()))->attachments());
    }

    // ---- Excel export ----

    public function test_export_to_excel(): void
    {
        $shipped = $this->order(['status' => 'shipped']);
        Invoices::assign($shipped);
        $this->order(['status' => 'cancelled']);

        $response = $this->actingAs($this->admin)->get('/nl/admin/orders/export?' . http_build_query([
            'from' => now()->subDay()->toDateString(), 'to' => now()->toDateString(), 'status' => 'all',
        ]));
        $response->assertOk()->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $file = $response->baseResponse->getFile()->getPathname();
        $reader = new Reader();
        $reader->open($file);
        $sheets = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            $rows = [];
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
            $sheets[$sheet->getName()] = $rows;
        }
        $reader->close();

        $this->assertSame(['Bestellingen', 'Orderregels', 'Creditfacturen'], array_keys($sheets));
        $orders = $sheets['Bestellingen'];
        $this->assertSame('Bestelling', $orders[0][0]);
        $this->assertCount(2, $orders);                                   // header + 1 (cancelled left out)
        $this->assertSame($shipped->number, $orders[1][0]);
        $this->assertSame($shipped->fresh()->invoice_number, $orders[1][1]);
        $this->assertEquals(121.0, $orders[1][15]);                        // total incl. VAT
        $this->assertEquals(21.0, $orders[1][17]);                         // VAT
        $this->assertEquals(100.0, $orders[1][18]);                        // total excl. VAT
        $this->assertSame('Black Abaya (54 · Zwart)', $sheets['Orderregels'][1][3]);
    }

    public function test_export_filters_and_access(): void
    {
        $this->order(['status' => 'cancelled']);

        $response = $this->actingAs($this->admin)->get('/en/admin/orders/export?status=cancelled');
        $response->assertOk();

        $this->actingAs($this->admin)->get('/en/admin/orders/export?from=2026-12-01&to=2026-01-01')->assertSessionHasErrors('to');
        $this->actingAs(User::factory()->create())->get('/en/admin/orders/export')->assertForbidden();
        $this->actingAs($this->admin)->get('/en/admin/orders')->assertSee('Export orders (Excel)');
    }
}
