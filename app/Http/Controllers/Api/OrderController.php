<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

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
