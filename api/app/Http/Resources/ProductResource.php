<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this-> id,
            'category_id' => $this->category_id,
            'name' => $this-> name,
            'description' => $this-> description,
            'price' => $this-> price,
            'image_url' => $this->imageUrl(),
            'stock' => $this-> stock,
            'available' => $this->active && $this->stock > 0,
            'active' => $this->active,
            'category' => new CategoryResource($this-> whenLoaded('category')),
        ];
    }

    private function imageUrl(): ?string
    {
        if (! $this->image_url) {
            return null;
        }

        if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
            return $this->image_url;
        }

        $baseUrl = rtrim(
            (string) config('filesystems.disks.public.url', url('/storage')),
            '/',
        );

        return $baseUrl.'/'.ltrim($this->image_url, '/');
    }
}
