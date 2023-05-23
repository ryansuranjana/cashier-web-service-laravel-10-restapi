<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Category\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
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
            'sku' => $this->sku,
            'name' => $this->name,
            'stock' => $this->stock,
            'price' => $this->price,
            'image' => Storage::url($this->image),
            'category' => new CategoryResource($this->category),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
