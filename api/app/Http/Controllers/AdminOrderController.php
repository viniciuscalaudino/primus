<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderStatusRequest;
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

    public function updateStatus(UpdateOrderStatusRequest $request, int $id)
    {
        $status = $request->validated('status');

        $order = $this->orderService->updateOrderStatus($id, $status);

        return new OrderResource($order);
    }
}
