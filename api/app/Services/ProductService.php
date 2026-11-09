<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function getAllProducts()
    {
        return Product::with('category')
            ->latest()
            ->paginate(12);
    }

    public function createProduct(array $data): Product
    {
        return Product::create($data);
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->load('category');
    }

    public function deactivateProduct(Product $product): Product
    {
        $product->update([
            'active' => false,
        ]);

        return $product;
    }
}
