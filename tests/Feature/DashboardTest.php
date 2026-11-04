<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertViewHasAll([
            'totalOmzet',
            'totalPengeluaran',
            'labaBersih',
            'lowStockProducts',
            'topProducts',
        ]);
    }

    public function test_dashboard_calculates_omzet_expenses_and_net_profit_correctly(): void
    {
        // Order Paid (included)
        Order::create([
            'invoice_number' => 'INV-202610-001',
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'order_date' => '2026-10-15',
            'total_amount' => 150000,
            'payment_status' => 'paid',
            'payment_method' => 'qris',
        ]);

        // Order Unpaid (should be excluded from omzet)
        Order::create([
            'invoice_number' => 'INV-202610-002',
            'customer_name' => 'Siti Nurhaliza',
            'customer_phone' => '081298765432',
            'order_date' => '2026-10-16',
            'total_amount' => 50000,
            'payment_status' => 'unpaid',
            'payment_method' => 'cash',
        ]);

        // Order Paid from different month (should be excluded)
        Order::create([
            'invoice_number' => 'INV-202609-001',
            'customer_name' => 'Ahmad Dani',
            'customer_phone' => '081200001111',
            'order_date' => '2026-09-10',
            'total_amount' => 80000,
            'payment_status' => 'paid',
            'payment_method' => 'transfer',
        ]);

        // Expense in October 2026
        Expense::create([
            'expense_date' => '2026-10-12',
            'category' => 'bahan_baku',
            'title' => 'Beli Daging Ayam 10 Kg',
            'amount' => 60000,
        ]);

        // Expense in September 2026 (should be excluded)
        Expense::create([
            'expense_date' => '2026-09-25',
            'category' => 'kemasan',
            'title' => 'Beli Box Kemasan',
            'amount' => 25000,
        ]);

        $response = $this->get('/?month=10&year=2026');

        $response->assertStatus(200);
        $this->assertEquals(150000, $response->viewData('totalOmzet'));
        $this->assertEquals(60000, $response->viewData('totalPengeluaran'));
        $this->assertEquals(90000, $response->viewData('labaBersih'));
    }

    public function test_dashboard_identifies_low_stock_products(): void
    {
        // Low stock (stock <= min_stock_alert)
        $lowProduct = Product::create([
            'name' => 'Ayam Marinasi Pedas 500g',
            'sku' => 'SM-AYM-PDS-01',
            'cost_price' => 20000,
            'selling_price' => 35000,
            'stock' => 3,
            'min_stock_alert' => 5,
        ]);

        // Normal stock
        Product::create([
            'name' => 'Ayam Marinasi Original 500g',
            'sku' => 'SM-AYM-ORI-01',
            'cost_price' => 20000,
            'selling_price' => 35000,
            'stock' => 15,
            'min_stock_alert' => 5,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $lowStockProducts = $response->viewData('lowStockProducts');
        $this->assertCount(1, $lowStockProducts);
        $this->assertEquals($lowProduct->id, $lowStockProducts->first()->id);
    }

    public function test_dashboard_ranks_top_five_products_by_paid_orders(): void
    {
        $productA = Product::create([
            'name' => 'Ayam Marinasi Garlic',
            'sku' => 'SM-AYM-GRL-01',
            'cost_price' => 22000,
            'selling_price' => 38000,
            'stock' => 20,
            'min_stock_alert' => 5,
        ]);

        $productB = Product::create([
            'name' => 'Ayam Marinasi BBQ',
            'sku' => 'SM-AYM-BBQ-01',
            'cost_price' => 24000,
            'selling_price' => 40000,
            'stock' => 20,
            'min_stock_alert' => 5,
        ]);

        $order = Order::create([
            'invoice_number' => 'INV-202610-003',
            'customer_name' => 'Customer Top',
            'order_date' => '2026-10-18',
            'total_amount' => 116000,
            'payment_status' => 'paid',
            'payment_method' => 'qris',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $productA->id,
            'quantity' => 2,
            'unit_cost' => 22000,
            'unit_price' => 38000,
            'subtotal' => 76000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $productB->id,
            'quantity' => 1,
            'unit_cost' => 24000,
            'unit_price' => 40000,
            'subtotal' => 40000,
        ]);

        $response = $this->get('/?month=10&year=2026');

        $response->assertStatus(200);
        $topProducts = $response->viewData('topProducts');
        $this->assertCount(2, $topProducts);
        $this->assertEquals($productA->id, $topProducts->first()->product_id);
        $this->assertEquals(2, $topProducts->first()->total_qty);
    }

    public function test_eloquent_relationships_work_properly(): void
    {
        $product = Product::create([
            'name' => 'Ayam Marinasi Teriyaki',
            'sku' => 'SM-AYM-TRY-01',
            'cost_price' => 23000,
            'selling_price' => 39000,
            'stock' => 10,
            'min_stock_alert' => 5,
        ]);

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 10,
            'reference' => 'Produksi Harian',
            'notes' => 'Batch 1',
        ]);

        $order = Order::create([
            'invoice_number' => 'INV-202610-004',
            'customer_name' => 'Test Relation',
            'order_date' => '2026-10-19',
            'total_amount' => 39000,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_cost' => 23000,
            'unit_price' => 39000,
            'subtotal' => 39000,
        ]);

        // Product relations
        $this->assertTrue($product->stockMovements->contains($movement));
        $this->assertTrue($product->orderItems->contains($orderItem));

        // Order relations
        $this->assertTrue($order->items->contains($orderItem));

        // OrderItem relations
        $this->assertEquals($product->id, $orderItem->product->id);
        $this->assertEquals($order->id, $orderItem->order->id);

        // StockMovement relation
        $this->assertEquals($product->id, $movement->product->id);
    }
}
