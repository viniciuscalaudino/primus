<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService) {}

    public function show(int $id)
    {
        $product = Product::with('category')
                ->where('active', true)
                ->findOrFail($id);

        return new ProductResource($product);
    }

    public function index(Request $request)
    {
        $products = Product::with('category')
                ->where('active', true)
                ->when($request->query('q'), function ($query, $q) {
                    $query -> where('name', 'like', '%' . $q . '%');
                })
                ->when($request->query('category'), function ($query, $category) {
                    $query -> where('category_id', $category);
                })
                 ->when($request->query('sort'), function ($query, $sort) {
                    match($sort){
                        'price_asc' => $query -> orderBy('price', 'asc'),
                        'price_desc' => $query -> orderBy('price', 'desc'),
                        'recent' => $query -> latest(),
                        default => $query -> latest(),
                    };
                },function ($query) {
                    $query -> latest();
                })
                ->orderByDesc('id')
                -> paginate(12);
        
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
        $product = Product::findOrFail($id);

        $product = $this->productService->updateProduct(
            $product,
            $request->validated()
        );

        return new ProductResource($product);
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);

        $this->productService->deactivateProduct($product);

        return response()->noContent();
    }
}
