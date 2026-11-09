<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Http\Resources\OrderResource;
use App\Models\Order;

class AdminOrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request)
    {
        $status = $request->query('status');

        if ($status !== null && !in_array($status, [
            Order::STATUS_AWAITING_PAYMENT,
            Order::STATUS_PAID,
            Order::STATUS_SHIPPED,
            Order::STATUS_DELIVERED,
            Order::STATUS_CANCELLED,
        ], true)) {
            abort(422, 'Status inválido.');
        }

        $orders = $this->orderService->getAllOrders($status);

        return OrderResource::collection($orders);
    }

    public function updateStatus(Request $request, int $id)
    {
        $status = $request->input('status');

        if (!in_array($status, [
            Order::STATUS_SHIPPED,
            Order::STATUS_DELIVERED,
        ], true)) {
            abort(422, 'Status inválido.');
        }

        $order = $this->orderService->updateOrderStatus($id, $status);

        return new OrderResource($order);
    }
}
