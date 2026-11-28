<?php

namespace Tests\Feature;

use App\Mail\BackInStock;
use App\Mail\RefundProcessed;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Support\CreditNotes;
use App\Support\Invoices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReturnsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $abaya;

    private ProductVariant $size54;

    private Product $mabkhara;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['shop.business' => ['name' => 'Rukn', 'address' => 'Damrak 1, 1012 LG Amsterdam', 'kvk' => '12345678', 'vat_id' => 'NL001234567B01', 'iban' => null]]);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        $this->abaya = Product::factory()->for($category)->create(['name' => 'Black Abaya', 'price' => 121, 'stock' => 1, 'is_active' => true]);
        $this->size54 = ProductVariant::create(['product_id' => $this->abaya->id, 'size' => '54', 'stock' => 1]);
        $this->mabkhara = Product::factory()->for($category)->create(['name' => 'Gold Mabkhara', 'price' => 30, 'stock' => 5, 'is_active' => true]);
    }

    /** A shipped order: 2 abayas (size 54) + 1 mabkhara + €4.95 shipping. */
    private function order(array $attributes = []): Order
    {
        $order = Order::create($attributes + [
            'status' => 'shipped', 'payment_status' => 'open', 'payment_method' => 'cod', 'locale' => 'nl',
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam', 'country' => 'NL',
            'subtotal' => 272, 'shipping_cost' => 4.95, 'total' => 276.95, 'vat_rate' => 21, 'vat_amount' => 48.07,
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $this->abaya->id, 'variant_id' => $this->size54->id, 'product_name' => 'Black Abaya', 'variant_label' => '54', 'quantity' => 2, 'price' => 121]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $this->mabkhara->id, 'product_name' => 'Gold Mabkhara', 'quantity' => 1, 'price' => 30]);

        return $order;
    }

    private function items(Order $order): array
    {
        return $order->items()->orderBy('id')->pluck('id')->all();
    }

    private function register(Order $order, array $data)
    {
        return $this->actingAs($this->admin)->post("/en/admin/orders/{$order->id}/returns", $data);
    }

    // ---- Creating ----

    public function test_a_partial_return_makes_a_numbered_credit_note(): void
    {
        $order = $this->order();
        [$abayaLine] = $this->items($order);

        $this->register($order, ['quantities' => [$abayaLine => 1], 'restock' => '1', 'reason' => 'Too long'])
            ->assertRedirect()->assertSessionHas('success');

        $order->refresh();
        $return = OrderReturn::sole();
        $year = now()->format('Y');
        $this->assertSame("{$year}-0001", $order->invoice_number);          // invoice made first
        $this->assertSame("{$year}-0002", $return->credit_note_number);    // next number of the same sequence
        $this->assertEquals(121.0, (float) $return->total);
        $this->assertEquals(0.0, (float) $return->shipping_amount);
        $this->assertEquals(21.0, (float) $return->vat_amount);
        $this->assertSame('Too long', $return->reason);
        $this->assertNull($return->refunded_at);
        $this->assertSame('shipped', $order->status);                     // partial: unchanged

        // Restocked: variant and product (whose stock is the sum).
        $this->assertSame(2, (int) $this->size54->fresh()->stock);
        $this->assertSame(2, (int) $this->abaya->fresh()->stock);

        Mail::assertNotQueued(RefundProcessed::class);                    // not refunded yet
    }

    public function test_returning_everything_with_shipping_marks_the_order_refunded(): void
    {
        $order = $this->order();
        [$abayaLine, $mabkharaLine] = $this->items($order);

        $this->register($order, ['quantities' => [$abayaLine => 2, $mabkharaLine => 1], 'refund_shipping' => '1']);

        $return = OrderReturn::sole();
        $this->assertEquals(276.95, (float) $return->total);
        $this->assertEquals(4.95, (float) $return->shipping_amount);
        $this->assertSame('refunded', $order->fresh()->status);
        // Not restocked when unticked.
        $this->assertSame(1, (int) $this->size54->fresh()->stock);
        $this->assertFalse(CreditNotes::canReturn($order->fresh()));
    }

    public function test_never_more_than_was_ordered_and_shipping_only_once(): void
    {
        $order = $this->order();
        [$abayaLine, $mabkharaLine] = $this->items($order);

        $this->register($order, ['quantities' => [$abayaLine => 1], 'refund_shipping' => '1']);
        $this->register($order, ['quantities' => [$abayaLine => 2]])->assertSessionHasErrors('quantities', null, 'return');
        $this->register($order, ['quantities' => []])->assertSessionHasErrors('quantities', null, 'return');

        $this->register($order, ['quantities' => [$abayaLine => 1, $mabkharaLine => 1], 'refund_shipping' => '1']);
        $second = OrderReturn::orderByDesc('id')->first();
        $this->assertEquals(0.0, (float) $second->shipping_amount);        // already refunded once
        $this->assertSame(2, OrderReturn::count());
    }

    public function test_orders_not_yet_invoiced_cannot_be_returned(): void
    {
        $order = $this->order(['status' => 'pending']);                   // pay on delivery, not shipped
        [$abayaLine] = $this->items($order);

        $this->assertFalse(CreditNotes::canReturn($order));
        $this->register($order, ['quantities' => [$abayaLine => 1]])->assertSessionHasErrors('quantities', null, 'return');
        $this->assertSame(0, OrderReturn::count());
    }

    public function test_restocking_tells_customers_waiting_for_it(): void
    {
        $this->size54->update(['stock' => 0]);
        $this->postJson("/en/products/{$this->abaya->slug}/notify", ['email' => 'wait@example.com', 'variant_id' => $this->size54->id])->assertOk();
        $order = $this->order();
        [$abayaLine] = $this->items($order);

        $this->register($order, ['quantities' => [$abayaLine => 1], 'restock' => '1']);

        Mail::assertQueued(BackInStock::class, fn ($mail) => $mail->hasTo('wait@example.com'));
    }

    // ---- Refund ----

    public function test_marking_refunded_emails_the_customer_with_the_credit_note(): void
    {
        $order = $this->order();
        [$abayaLine] = $this->items($order);
        $this->register($order, ['quantities' => [$abayaLine => 1]]);
        $return = OrderReturn::sole();

        $this->actingAs($this->admin)->get("/en/admin/orders/{$order->id}")->assertOk()
            ->assertSee('Returns &amp; credit notes', false)->assertSee($return->credit_note_number)
            ->assertSee('Not refunded yet')->assertSee('transfer €121.00 to the customer');

        $this->actingAs($this->admin)->post("/en/admin/returns/{$return->id}/refunded", ['notify' => '1'])->assertRedirect();

        $this->assertNotNull($return->fresh()->refunded_at);
        Mail::assertQueued(RefundProcessed::class, function (RefundProcessed $mail) use ($return) {
            $mail->assertSeeInHtml('121,00')->assertSeeInHtml('per bankoverschrijving')->assertSeeInText($return->credit_note_number);
            [$attachment] = $mail->attachments();
            $this->assertSame(CreditNotes::filename($return->fresh()), $attachment->as);
            $this->assertSame('application/pdf', $attachment->mime);

            return $mail->hasTo('f@example.com');
        });

        // Marking twice sends nothing more.
        $this->actingAs($this->admin)->post("/en/admin/returns/{$return->id}/refunded", ['notify' => '1']);
        Mail::assertQueued(RefundProcessed::class, 1);
    }

    public function test_already_refunded_at_registration(): void
    {
        $order = $this->order(['payment_method' => 'mollie', 'payment_status' => 'paid', 'paid_at' => now(), 'locale' => 'en']);
        [, $mabkharaLine] = $this->items($order);

        $this->register($order, ['quantities' => [$mabkharaLine => 1], 'refunded' => '1', 'notify' => '1']);

        $this->assertNotNull(OrderReturn::sole()->refunded_at);
        Mail::assertQueued(RefundProcessed::class, fn ($mail) => $mail->locale === 'en');
    }

    // ---- The credit note ----

    public function test_the_pdf_has_the_required_details(): void
    {
        $order = $this->order();
        [$abayaLine] = $this->items($order);
        $this->register($order, ['quantities' => [$abayaLine => 1], 'refund_shipping' => '1', 'reason' => 'Too long']);
        $return = OrderReturn::sole();

        $this->assertStringStartsWith('%PDF-', CreditNotes::pdf($return));

        app()->setLocale('nl');
        $html = view('invoices.credit-note', ['return' => $return->load('items.orderItem', 'order'), 'order' => $order->fresh(), 'business' => config('shop.business')])->render();
        foreach (['Creditfactuur', $return->credit_note_number, 'Crediteert factuur', $order->fresh()->invoice_number,
                  'Black Abaya (54)', 'Terugbetaalde verzendkosten', '− € 125,95', 'Btw 21%', 'NL001234567B01', 'Too long'] as $text) {
            $this->assertStringContainsString($text, $html, $text);
        }

        $this->actingAs($this->admin)->get("/en/admin/returns/{$return->id}/credit-note")->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="Rukn-creditfactuur-' . $return->credit_note_number . '.pdf"');
    }

    public function test_customer_gets_it_from_the_private_order_link(): void
    {
        $order = $this->order();
        [$abayaLine] = $this->items($order);
        $this->register($order, ['quantities' => [$abayaLine => 1]]);
        $return = OrderReturn::sole();
        auth()->logout();

        $this->get($return->creditNoteUrl('en'))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->get("/en/orders/credit-note/{$order->id}/" . str_repeat('x', 40) . "/{$return->id}")->assertNotFound();
        $this->get($order->privateUrl('en'))->assertSee('Credit note ' . $return->credit_note_number)->assertSee('being refunded');
    }

    // ---- Accounting ----

    public function test_export_and_reports_include_credit_notes(): void
    {
        $order = $this->order();
        [$abayaLine] = $this->items($order);
        $this->register($order, ['quantities' => [$abayaLine => 1]]);

        $this->actingAs($this->admin)->get('/en/admin/reports')->assertSee('Refunded (credit notes)')->assertSee('€121.00');
        $this->actingAs($this->admin)->get('/en/admin/orders/export?status=all')->assertOk();
    }

    public function test_only_admins(): void
    {
        $order = $this->order();
        [$abayaLine] = $this->items($order);

        $this->actingAs(User::factory()->create())->post("/en/admin/orders/{$order->id}/returns", ['quantities' => [$abayaLine => 1]])->assertForbidden();
        $this->assertSame(0, OrderReturn::count());
    }

    public function test_translated(): void
    {
        $order = $this->order();

        $this->actingAs($this->admin)->get("/ar/admin/orders/{$order->id}")->assertSee('المرتجعات والفواتير الدائنة')->assertSee('تسجيل إرجاع');
        $this->actingAs($this->admin)->get("/nl/admin/orders/{$order->id}")->assertSee('Retour registreren');
    }
}
