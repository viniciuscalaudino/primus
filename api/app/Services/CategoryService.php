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

      public function updateCategory(Category $category, array $data): Category
    {
        $category->update($data);

        return $category;
    }

    public function deleteCategory(Category $category): void
    {
        if ($category->products()->exists()) {
            abort(409, 'Não é possível excluir uma categoria que possui produtos.');
        }

        $category->delete();
    }
}
