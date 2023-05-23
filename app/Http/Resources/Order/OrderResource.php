<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\OrderProduct\OrderProductCollection;
use App\Http\Resources\Payment\PaymentResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'total_price' => $this->total_price,
            'total_paid' => $this->total_paid,
            'total_return' => $this->total_return,
            'receipt_code' => $this->receipt_code,
            'user' => new UserResource($this->user),
            'payment' => new PaymentResource($this->payment),
            'products' => new OrderProductCollection($this->products),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
