<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\OrderItemResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'shipping' => $this->shipping,
            'total' => $this->total,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            'shipping_address' => [
            'recipient_name' => $this->shipping_recipient_name,
            'zip_code' => $this->shipping_zip_code,
            'street' => $this->shipping_street,
            'number' => $this->shipping_number,
            'complement' => $this->shipping_complement,
            'neighborhood' => $this->shipping_neighborhood,
            'city' => $this->shipping_city,
            'state' => $this->shipping_state,
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}