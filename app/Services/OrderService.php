<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function getAllOrders()
    {
        return Order::with('items.variant.product')->latest()->get();
    }

    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {

            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'governorate' => $data['governorate'],
                'shipping_address' => $data['shipping_address'],
                'notes' => $data['notes'] ?? null,
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($data['items'] as $item) {
                $variant = ProductVariant::find($item['product_variant_id']);

                if ($variant->stock_quantity < $item['quantity']) {
                    throw new \Exception("الكمية المطلوبة من المنتج غير متوفرة في المخزن.");
                }

                $subtotal = $variant->price * $item['quantity'];
                $totalAmount += $subtotal;

                $order->items()->create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $variant->price,
                    'subtotal' => $subtotal,
                ]);

                $variant->decrement('stock_quantity', $item['quantity']);
            }

            $order->update(['total_amount' => $totalAmount]);

            return $order->load('items.variant.product');
        });
    }

    public function updateOrderStatus(Order $order, $status)
    {
        $order->update(['status' => $status]);
        return $order;
    }
}
