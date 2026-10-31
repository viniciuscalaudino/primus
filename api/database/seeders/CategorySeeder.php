<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Proteínas', 'description' => 'Whey protein, caseína, albumina e outras fontes de proteína para ganho e recuperação muscular.'],
            ['name' => 'Creatina e aminoácidos', 'description' => 'Creatina, BCAA, glutamina e aminoácidos para força, desempenho e recuperação.'],
            ['name' => 'Pré-treino e energia', 'description' => 'Pré-treinos, cafeína e termogênicos para mais energia e foco durante o treino.'],
            ['name' => 'Acessórios', 'description' => 'Coqueteleiras, luvas, cintos e acessórios para o seu treino.'],
        ];

        foreach($categories as $category) {
            Category::create($category);
        }
    }
}
