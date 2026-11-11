<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public function getAllCategories()
    {
        return Category::all();
    }

    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }

    public function updateCategoryById(int $id, array $data): Category
    {
        $category = Category::findOrFail($id);
        $category->update($data);

        return $category;
    }

    public function deleteCategoryById(int $id): void
    {
        $category = Category::findOrFail($id);

        if ($category->products()->exists()) {
            abort(409, 'Não é possível excluir uma categoria que possui produtos.');
        }

        $category->delete();
    }
}
