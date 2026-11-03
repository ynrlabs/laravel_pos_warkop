<?php

namespace Tests\Feature;

use App\Filament\Admin\Widgets\TopProductsWidget;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_or_pos(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_admin_dashboard_is_accessible_for_super_admin(): void
    {
        $outlet = Outlet::create([
            'name' => 'Warkop Pusat',
            'code' => 'WKP-01',
            'is_active' => true,
        ]);

        $admin = User::create([
            'outlet_id' => $outlet->id,
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_top_products_widget_renders_without_type_error(): void
    {
        $outlet = Outlet::create([
            'name' => 'Warkop Pusat',
            'code' => 'WKP-01',
            'is_active' => true,
        ]);

        $admin = User::create([
            'outlet_id' => $outlet->id,
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'outlet_id' => $outlet->id,
            'name' => 'Minuman',
            'slug' => 'minuman',
        ]);

        $product = Product::create([
            'outlet_id' => $outlet->id,
            'category_id' => $category->id,
            'name' => 'Kopi Tubruk Juara',
            'slug' => 'kopi-tubruk-juara',
            'base_price' => 12000,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-001',
            'outlet_id' => $outlet->id,
            'user_id' => $admin->id,
            'status' => 'paid',
            'payment_status' => 'paid',
            'subtotal' => 24000,
            'total_amount' => 24000,
            'order_type' => 'dine_in',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'variant_name' => 'Panas',
            'quantity' => 2,
            'unit_price' => 12000,
            'subtotal' => 24000,
        ]);

        $this->actingAs($admin);

        Livewire::test(TopProductsWidget::class)
            ->assertSuccessful()
            ->assertSee('5 Produk Terlaris')
            ->assertSee('Kopi Tubruk Juara');
    }
}
