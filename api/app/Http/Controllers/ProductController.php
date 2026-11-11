<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService) {}

    public function show(int $id)
    {
        return new ProductResource(
            $this->productService->getActiveProduct($id)
        );
    }

    public function index(Request $request)
    {
        $products = $this->productService->getCatalog(
            $request->only(['q', 'category', 'sort'])
        );

        return ProductResource::collection($products);
    }

    public function adminIndex()
    {
        $products = $this->productService->getAllProducts();

        return ProductResource::collection($products);
    }

    public function store(ProductRequest $request)
    {
        $product = $this->productService->createProduct(
            $request->validated()
        );

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function update(ProductRequest $request, int $id)
    {
        $product = $this->productService->updateProductById(
            $id,
            $request->validated()
        );

        return new ProductResource($product);
    }

    public function destroy(int $id)
    {
        $this->productService->deactivateProductById($id);

        return response()->noContent();
    }
}
