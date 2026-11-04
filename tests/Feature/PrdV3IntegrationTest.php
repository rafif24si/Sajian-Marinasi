<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrdV3IntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test all pages return HTTP 200 without any errors when database is completely empty (0 records).
     */
    public function test_all_pages_render_successfully_on_empty_database(): void
    {
        $this->get(route('dashboard'))->assertStatus(200);
        $this->get(route('products.index'))->assertStatus(200);
        $this->get(route('expenses.index'))->assertStatus(200);
        $this->get(route('orders.index'))->assertStatus(200);
        $this->get(route('orders.create'))->assertStatus(200);
        $this->get(route('reports.index'))->assertStatus(200);
    }

    /**
     * Test full real-time lifecycle:
     * Product Creation -> Restock -> Expense -> Paid Order (Stock Deduction & Omzet) -> Report & Dashboard Sync
     */
    public function test_complete_real_time_flow(): void
    {
        // 1. Create a Product with initial stock of 10
        $productResponse = $this->post(route('products.store'), [
            'name' => 'Ayam Marinasi Bawang Putih',
            'sku' => 'SM-AYM-BP01',
            'cost_price' => 20000,
            'selling_price' => 35000,
            'stock' => 10,
            'min_stock_alert' => 5,
        ]);
        $productResponse->assertRedirect(route('products.index'));

        $product = Product::where('sku', 'SM-AYM-BP01')->firstOrFail();
        $this->assertEquals(10, $product->stock);

        // Verify initial StockMovement
        $initialMovement = StockMovement::where('product_id', $product->id)->first();
        $this->assertNotNull($initialMovement);
        $this->assertEquals('in', $initialMovement->type);
        $this->assertEquals(10, $initialMovement->quantity);
        $this->assertEquals('Stok Awal', $initialMovement->reference);

        // 2. Restock Product (+5 pcs)
        $this->post(route('products.add-stock', $product->id), [
            'type' => 'in',
            'quantity' => 5,
            'reference' => 'Batch Pagi',
            'notes' => 'Hasil ungkep pagi',
        ])->assertRedirect(route('products.index'));

        $product->refresh();
        $this->assertEquals(15, $product->stock);

        // 3. Record an Expense
        $this->post(route('expenses.store'), [
            'expense_date' => date('Y-m-d'),
            'category' => 'bahan_baku',
            'title' => 'Belanja Bawang & Bumbu',
            'amount' => 45000,
            'notes' => 'Pasar Induk',
        ])->assertRedirect(route('expenses.index'));

        $this->assertDatabaseHas('expenses', [
            'title' => 'Belanja Bawang & Bumbu',
            'amount' => 45000,
        ]);

        // 4. Create a Paid Order for 2 pcs
        $this->post(route('orders.store'), [
            'customer_name' => 'Ibu Siti Khadijah',
            'customer_phone' => '081234567890',
            'order_date' => date('Y-m-d'),
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ])->assertRedirect();

        $product->refresh();
        // Stock should be deducted from 15 to 13
        $this->assertEquals(13, $product->stock);

        $order = Order::where('customer_name', 'Ibu Siti Khadijah')->firstOrFail();
        $this->assertEquals(70000, $order->total_amount); // 2 x 35000
        $this->assertEquals('paid', $order->payment_status);

        // Verify out StockMovement
        $outMovement = StockMovement::where('product_id', $product->id)
            ->where('type', 'out')
            ->first();
        $this->assertNotNull($outMovement);
        $this->assertEquals(2, $outMovement->quantity);
        $this->assertEquals($order->invoice_number, $outMovement->reference);

        // 5. Verify Dashboard Analytics reflect exact real-time values
        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $this->assertEquals(70000, $dashboardResponse->viewData('totalOmzet'));
        $this->assertEquals(45000, $dashboardResponse->viewData('totalPengeluaran'));
        $this->assertEquals(25000, $dashboardResponse->viewData('labaBersih')); // 70000 - 45000
        $this->assertEquals(1, $dashboardResponse->viewData('paidOrdersCount'));

        // 6. Verify Reports Analytics
        $reportResponse = $this->get(route('reports.index'));
        $reportResponse->assertStatus(200);
        $this->assertEquals(70000, $reportResponse->viewData('totalOmzet'));
        $this->assertEquals(40000, $reportResponse->viewData('totalHpp')); // 2 x 20000
        $this->assertEquals(30000, $reportResponse->viewData('labaKotor')); // 70000 - 40000
        $this->assertEquals(45000, $reportResponse->viewData('totalPengeluaran'));
        $this->assertEquals(25000, $reportResponse->viewData('arusKasBersih')); // 70000 - 45000
    }

    /**
     * Test Unpaid order flow: stock is not deducted until marked as paid.
     */
    public function test_unpaid_order_does_not_deduct_stock_until_marked_as_paid(): void
    {
        $product = Product::create([
            'name' => 'Bebek Marinasi',
            'sku' => 'SM-BBK-01',
            'cost_price' => 50000,
            'selling_price' => 75000,
            'stock' => 10,
            'min_stock_alert' => 2,
        ]);

        // Create UNPAID order
        $this->post(route('orders.store'), [
            'customer_name' => 'Pak Rudi',
            'customer_phone' => '081299998888',
            'order_date' => date('Y-m-d'),
            'payment_status' => 'unpaid',
            'payment_method' => 'transfer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                ],
            ],
        ]);

        $product->refresh();
        // Stock should STILL be 10 because order is UNPAID
        $this->assertEquals(10, $product->stock);

        $order = Order::where('customer_name', 'Pak Rudi')->firstOrFail();
        $this->assertEquals('unpaid', $order->payment_status);

        // Now mark as paid
        $this->patch(route('orders.mark-paid', $order->id))->assertRedirect();

        $product->refresh();
        $order->refresh();

        // Stock should NOW be 7
        $this->assertEquals(7, $product->stock);
        $this->assertEquals('paid', $order->payment_status);
    }

    /**
     * Test Deleting a paid order restores the stock to inventory.
     */
    public function test_deleting_paid_order_restores_stock(): void
    {
        $product = Product::create([
            'name' => 'Nila Marinasi',
            'sku' => 'SM-NL-01',
            'cost_price' => 15000,
            'selling_price' => 25000,
            'stock' => 10,
            'min_stock_alert' => 3,
        ]);

        // Create PAID order
        $this->post(route('orders.store'), [
            'customer_name' => 'Pak Joko',
            'order_date' => date('Y-m-d'),
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 4,
                ],
            ],
        ]);

        $product->refresh();
        $this->assertEquals(6, $product->stock);

        $order = Order::where('customer_name', 'Pak Joko')->firstOrFail();

        // Delete order
        $this->delete(route('orders.destroy', $order->id))->assertRedirect(route('orders.index'));

        $product->refresh();
        // Stock must be restored from 6 to 10
        $this->assertEquals(10, $product->stock);
    }
}
