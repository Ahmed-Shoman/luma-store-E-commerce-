<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $orders = $this->orderService->getAllOrders();
        return $this->successResponse($orders, 'تم جلب الطلبات بنجاح');
    }

    public function store(OrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder($request->validated());
            return $this->successResponse($order, 'تم تسجيل الطلب بنجاح', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show(Order $order)
    {
        $order->load('items.variant.product');
        return $this->successResponse($order, 'تفاصيل الطلب');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|in:pending,processing,shipped,completed,cancelled']);

        $updatedOrder = $this->orderService->updateOrderStatus($order, $request->status);
        return $this->successResponse($updatedOrder, 'تم تحديث حالة الطلب بنجاح');
    }
}
