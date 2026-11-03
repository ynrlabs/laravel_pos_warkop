<?php

namespace Tests\Feature;

use App\Events\OrderCreated;
use App\Events\PrintReceipt;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PosTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected Outlet $outlet;
    protected User $cashier;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->outlet = Outlet::create([
            'name' => 'Warkop Pusat',
            'code' => 'WKP-01',
            'is_active' => true,
            'settings' => [
                'tax_rate' => 0.11,
                'service_charge' => 0.05,
                'rounding' => 100,
            ],
        ]);

        $this->cashier = User::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Kasir Warkop',
            'email' => 'kasir@warkop.com',
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $category = Category::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Kopi',
            'slug' => 'kopi',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'outlet_id' => $this->outlet->id,
            'category_id' => $category->id,
            'name' => 'Kopi Susu Gula Aren',
            'slug' => 'kopi-susu-gula-aren',
            'base_price' => 15000,
            'type' => 'finished_good',
            'is_active' => true,
        ]);
    }

    public function test_cashier_can_open_and_close_shift(): void
    {
        $this->actingAs($this->cashier);

        // 1. Open shift
        $openResponse = $this->postJson(route('pos.shift.open'), [
            'opening_cash' => 200000,
            'notes' => 'Modal awal pecahan 50rb',
        ]);

        $openResponse->assertStatus(200);
        $openResponse->assertJson(['success' => true]);

        $shift = Shift::where('user_id', $this->cashier->id)->where('status', 'opened')->first();
        $this->assertNotNull($shift);
        $this->assertEquals(200000, $shift->opening_cash);

        // 2. Close shift
        $closeResponse = $this->postJson(route('pos.shift.close'), [
            'actual_cash' => 200000,
            'variance_notes' => null,
        ]);

        $closeResponse->assertStatus(200);
        $closeResponse->assertJson(['success' => true]);

        $shift->refresh();
        $this->assertEquals('closed', $shift->status);
        $this->assertNotNull($shift->summary);
    }

    public function test_process_order_requires_active_shift(): void
    {
        $this->actingAs($this->cashier);

        $response = $this->postJson(route('pos.order.process'), [
            'cart' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 1,
                    'base_price' => 15000,
                    'subtotal' => 15000,
                ],
            ],
            'order_type' => 'takeaway',
            'payment' => [
                'method' => 'cash',
                'cash_received' => 20000,
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_process_order_cash_dispatches_events_and_finalizes_order(): void
    {
        Event::fake([
            OrderCreated::class,
            PrintReceipt::class,
        ]);

        $this->actingAs($this->cashier);

        // Open shift first
        $this->postJson(route('pos.shift.open'), [
            'opening_cash' => 100000,
        ])->assertStatus(200);

        // Process cash order
        $response = $this->postJson(route('pos.order.process'), [
            'cart' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 2,
                    'base_price' => 15000,
                    'subtotal' => 30000,
                ],
            ],
            'order_type' => 'takeaway',
            'payment' => [
                'method' => 'cash',
                'cash_received' => 50000,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        Event::assertDispatched(OrderCreated::class);
        Event::assertDispatched(PrintReceipt::class);

        $shift = Shift::where('user_id', $this->cashier->id)->where('status', 'opened')->first();
        $this->assertEquals(1, $shift->transaction_count);
    }
}
