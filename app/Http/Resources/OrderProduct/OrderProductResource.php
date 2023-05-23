<?php

namespace App\Http\Resources\OrderProduct;

use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
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
            'order_id' => $this->pivot->order_id,
            'product' => new ProductResource($this),
            'qty' => $this->pivot->qty,
            'price' => $this->pivot->total_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
