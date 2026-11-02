<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
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
}
