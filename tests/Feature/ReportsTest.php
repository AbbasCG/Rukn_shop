<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Support\SalesReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-11-28 12:00:00', 'Europe/Amsterdam'));
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function order(string $placedAt, float $total, array $attributes = []): Order
    {
        $order = Order::create($attributes + [
            'status' => 'pending', 'payment_status' => 'open', 'payment_method' => 'cod', 'locale' => 'nl',
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam', 'country' => 'NL',
            'subtotal' => $total, 'shipping_cost' => 0, 'total' => $total, 'vat_rate' => 21, 'vat_amount' => round($total * 21 / 121, 2),
        ]);
        $order->forceFill(['created_at' => Carbon::parse($placedAt, 'Europe/Amsterdam')->utc()])->save();

        return $order;
    }

    public function test_what_counts_as_a_sale(): void
    {
        $this->order('2026-11-20 10:00', 121);                                               // pay on delivery: counts
        $this->order('2026-11-20 11:00', 60.50, ['payment_method' => 'mollie', 'payment_status' => 'paid']);
        $this->order('2026-11-20 12:00', 999, ['payment_method' => 'mollie', 'payment_status' => 'open']);   // never paid
        $this->order('2026-11-20 13:00', 999, ['status' => 'cancelled']);

        $data = (new SalesReport(...SalesReport::period('30d')))->build();

        $this->assertSame(181.5, $data['totals']['revenue']);
        $this->assertSame(2, $data['totals']['orders']);
        $this->assertSame(90.75, $data['totals']['average']);
        $this->assertSame(31.5, $data['totals']['vat']);
        $this->assertSame(150.0, $data['totals']['excl_vat']);
    }

    public function test_days_follow_dutch_time_and_empty_days_are_included(): void
    {
        // 23:30 in Amsterdam on the 27th is 22:30 UTC: still the 27th.
        $this->order('2026-11-27 23:30', 50);
        $this->order('2026-11-28 00:30', 20);

        $data = (new SalesReport(...SalesReport::period('7d')))->build();
        $buckets = collect($data['buckets'])->keyBy('key');

        $this->assertCount(7, $data['buckets']);
        $this->assertSame('2026-11-22', $data['buckets'][0]['key']);
        $this->assertSame(50.0, $buckets['2026-11-27']['revenue']);
        $this->assertSame(20.0, $buckets['2026-11-28']['revenue']);
        $this->assertSame(0, $buckets['2026-11-25']['orders']);
    }

    public function test_comparison_with_the_previous_period(): void
    {
        $this->order('2026-11-25 10:00', 150);          // this 7-day period
        $this->order('2026-11-18 10:00', 100);          // the 7 days before

        $data = (new SalesReport(...SalesReport::period('7d')))->build();

        $this->assertSame(100.0, $data['previous']['revenue']);
        $this->assertSame(50.0, $data['change']['revenue']);
        $this->assertSame(0.0, $data['change']['orders']);

        $empty = (new SalesReport(...SalesReport::period('this_month')))->previous();
        $this->assertSame('2026-10-04', $empty->from->toDateString());   // same length, just before
    }

    public function test_long_periods_are_grouped_by_month(): void
    {
        $this->order('2026-03-05 10:00', 40);
        $this->order('2026-03-25 10:00', 60);

        $report = new SalesReport(...SalesReport::period('this_year'));
        $data = $report->build();

        $this->assertFalse($report->isDaily());
        $this->assertCount(11, $data['buckets']);                     // Jan – Nov
        $this->assertSame(100.0, collect($data['buckets'])->firstWhere('key', '2026-03')['revenue']);
    }

    public function test_named_periods(): void
    {
        [$from, $to] = SalesReport::period('last_month');
        $this->assertSame(['2026-10-01', '2026-10-31'], [$from->toDateString(), $to->toDateString()]);

        [$from, $to] = SalesReport::period('custom', '2026-01-10', '2026-01-20');
        $this->assertSame(11, (new SalesReport($from, $to))->days());

        // A period over the clock change still counts whole days.
        [$from, $to] = SalesReport::period('custom', '2026-10-20', '2026-10-30');
        $this->assertSame(11, (new SalesReport($from, $to))->days());
    }

    public function test_page(): void
    {
        $this->order('2026-11-25 10:00', 150, ['shipping_cost' => 4.95, 'coupon_code' => null]);

        $this->actingAs($this->admin)->get('/en/admin/reports')->assertOk()
            ->assertSee('Reports')->assertSee('Last 30 days')
            ->assertSee('€150.00')->assertSee('Sales per day')
            ->assertSee('<svg', false)
            ->assertSee('Show as table')
            ->assertSee('No sales in the previous period to compare with')
            ->assertSee(route('admin.orders.export', ['locale' => 'en', 'from' => '2026-10-30', 'to' => '2026-11-28', 'status' => 'all']));

        $this->actingAs($this->admin)->get('/en/admin/reports?range=this_year')->assertOk()->assertSee('Sales per month');
        $this->actingAs($this->admin)->get('/en/admin/reports?range=custom&from=2026-01-01&to=2026-01-31')->assertOk()->assertSee('No sales in this period.');
        $this->actingAs($this->admin)->get('/en/admin/reports?range=custom&from=2026-02-01&to=2026-01-01')->assertSessionHasErrors('to');
        $this->actingAs($this->admin)->get('/en/admin/reports?range=nonsense')->assertSessionHasErrors('range');
    }

    public function test_translated_and_in_the_menu(): void
    {
        $this->actingAs($this->admin)->get('/ar/admin/reports')->assertOk()->assertSee('التقارير')->assertSee('آخر 30 يوماً');
        $this->actingAs($this->admin)->get('/nl/admin/reports')->assertSee('Rapporten');
        $this->actingAs($this->admin)->get('/en/admin')->assertSee(route('admin.reports.index', ['locale' => 'en']), false);
    }

    public function test_only_admins(): void
    {
        $this->actingAs(User::factory()->create())->get('/en/admin/reports')->assertForbidden();
        auth()->logout();
        $this->get('/en/admin/reports')->assertRedirect(route('login', ['locale' => 'en']));
    }
}
