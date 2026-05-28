<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Events\OrderCreated;

class OrderController extends Controller
{
    /**
     * GET /api/orders
     */
    public function index(Request $request)
    {
        $query = Order::query()
            ->with('items')
            ->select([
                'id',
                'customer_name',
                'customer_phone',
                'customer_address',
                'total',
                'status',
                'created_at',
                'updated_at',
            ])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by phone or name
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(20);

        return OrderResource::collection($orders);
    }

    /**
     * GET /api/orders/{id}
     */
    public function show($id)
    {
        $order = Order::with('items')->findOrFail($id);

        return new OrderResource($order);
    }

    /**
     * POST /api/orders
     */
    public function store(Request $request)
    {
        logger()->info('STEP 1: STORE METHOD HIT', [
            'time' => microtime(true),
        ]);
        $validated = $request->validate([
            'customer.name'        => 'required|string|max:255',
            'customer.phone'       => 'required|string|max:20',
            'customer.address'     => 'required|string|max:500',
            'items'                => 'required|array|min:1',
            'items.*.productId'  => 'required|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.price'        => 'required|numeric|min:0',
            'total'                => 'required|numeric|min:0',
        ]);

        $order = Order::create([
            'customer_name'    => $validated['customer']['name'],
            'customer_phone'   => $validated['customer']['phone'],
            'customer_address' => $validated['customer']['address'],
            'total'            => $validated['total'],
            'status'           => 'pending',
        ]);

        foreach ($request->input('items') as $item) {
            $order->items()->create([
                'product_id'      => $item['productId'],
                'product_name'    => $item['name'],
                'product_name_en' => $item['nameEn']  ?? null,
                'quantity'        => $item['quantity'],
                'price'           => $item['price'],
                'total'           => $item['price'] * $item['quantity'],
                'size'            => $item['size']    ?? null,
            ]);
        }

        // ✅ Dispatch event
        event(new OrderCreated($order));

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully.',
            'data'    => new OrderResource($order->load('items')),
        ], 201);
    }

    /**
     * PATCH /api/orders/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return new OrderResource($order->load('items'));
    }

    /**
     * DELETE /api/orders/{id}
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully'
        ]);
    }
}
