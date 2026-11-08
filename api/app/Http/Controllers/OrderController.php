<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function store(StoreOrderRequest $request)
    {
        $order = $this->orderService->createOrder(
            $request->user(),
            $request->validated()['address_id']
        );

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function index(Request $request)
    {
        $orders = $this->orderService->getUserOrders($request->user());
        
        return OrderResource::collection($orders);
    }

    public function show(Request $request, int $id)
    {
        $order = $this->orderService->getOrder(
            $request->user(),
            $id
        );

        return new OrderResource($order);
    }

    public function pay(Request $request, int $id)
    {
        $order = $this->orderService->payOrder(
            $request->user(),
            $id
        );

        return new OrderResource($order);
    }

    public function cancel(Request $request, int $id)
    {
        $order = $this->orderService->cancelOrder(
            $request->user(),
            $id
        );

        return new OrderResource($order);
    }
}