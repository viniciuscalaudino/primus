<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}

    public function index()
    {
        $categories = $this->categoryService->getAllCategories();

        return CategoryResource::collection($categories);
    }

    public function store(CategoryRequest $request)
    {
        $category = $this->categoryService->createCategory(
            $request->validated()
        );

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function update(CategoryRequest $request, int $id)
    {
        $category = $this->categoryService->updateCategoryById(
            $id,
            $request->validated()
        );

        return new CategoryResource($category);
    }

    public function destroy(int $id)
    {
        $this->categoryService->deleteCategoryById($id);

        return response()->noContent();
    }
}
