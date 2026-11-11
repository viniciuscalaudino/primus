<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProductService
{
    public function getCatalog(array $filters)
    {
        return Product::with('category')
            ->where('active', true)
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->when($filters['category'] ?? null, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($filters['sort'] ?? 'recent', function ($query, $sort) {
                match ($sort) {
                    'price_asc' => $query->orderBy('price', 'asc'),
                    'price_desc' => $query->orderBy('price', 'desc'),
                    default => $query->latest(),
                };
            })
            ->orderByDesc('id')
            ->paginate(12);
    }

    public function getActiveProduct(int $id): Product
    {
        return Product::with('category')
            ->where('active', true)
            ->findOrFail($id);
    }

    public function updateProductById(int $id, array $data): Product
    {
        $product = Product::findOrFail($id);

        return $this->updateProduct($product, $data);
    }

    public function deactivateProductById(int $id): void
    {
        $product = Product::findOrFail($id);

        $product->update(['active' => false]);
    }

    public function getAllProducts()
    {
        return Product::with('category')
            ->latest()
            ->paginate(12);
    }

    public function createProduct(array $data): Product
    {
        $image = $data['image'] ?? null;
        unset($data['image']);

        $imagePath = $image instanceof UploadedFile
            ? $image->store('products', 'public')
            : null;

        if ($image instanceof UploadedFile && ! $imagePath) {
            throw new RuntimeException('Não foi possível armazenar a imagem do produto.');
        }

        if ($imagePath) {
            $data['image_url'] = $imagePath;
        }

        try {
            return Product::create($data)->load('category');
        } catch (Throwable $exception) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $image = $data['image'] ?? null;
        unset($data['image']);

        $newImagePath = $image instanceof UploadedFile
            ? $image->store('products', 'public')
            : null;

        if ($image instanceof UploadedFile && ! $newImagePath) {
            throw new RuntimeException('Não foi possível armazenar a imagem do produto.');
        }
        $oldImagePath = $product->image_url;

        if ($newImagePath) {
            $data['image_url'] = $newImagePath;
        }

        try {
            $product->update($data);
        } catch (Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath && str_starts_with((string) $oldImagePath, 'products/')) {
            Storage::disk('public')->delete($oldImagePath);
        }

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
