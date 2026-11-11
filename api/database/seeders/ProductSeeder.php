<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $category_ids = Category::pluck('id', 'name');

        $products = [
            // Proteínas
            ['category' => 'Proteínas', 'name' => 'Whey Protein Concentrado 900g', 'description' => 'Proteína do soro do leite com 24g de proteína por dose.', 'price' => 119.90, 'stock' => 40],
            ['category' => 'Proteínas', 'name' => 'Whey Protein Isolado 900g', 'description' => 'Isolado com baixo teor de lactose e carboidratos.', 'price' => 189.90, 'stock' => 25],
            ['category' => 'Proteínas', 'name' => 'Whey Protein Concentrado 1,8kg', 'description' => 'Embalagem econômica de whey concentrado.', 'price' => 219.90, 'stock' => 18],
            ['category' => 'Proteínas', 'name' => 'Caseína 900g', 'description' => 'Proteína de absorção lenta, ideal antes de dormir.', 'price' => 139.90, 'stock' => 15],
            ['category' => 'Proteínas', 'name' => 'Albumina 500g', 'description' => 'Proteína da clara do ovo em pó.', 'price' => 59.90, 'stock' => 60],
            ['category' => 'Proteínas', 'name' => 'Proteína Vegetal 600g', 'description' => 'Blend de proteínas de ervilha e arroz, sem lactose.', 'price' => 99.90, 'stock' => 22],
            ['category' => 'Proteínas', 'name' => 'Hipercalórico 3kg', 'description' => 'Composto de carboidratos e proteínas para ganho de massa.', 'price' => 109.90, 'stock' => 0],
            ['category' => 'Proteínas', 'name' => 'Barra de Proteína (caixa com 12)', 'description' => 'Barras com 15g de proteína, sabor chocolate.', 'price' => 89.90, 'stock' => 35],

            // Creatina e aminoácidos
            ['category' => 'Creatina e aminoácidos', 'name' => 'Creatina Monohidratada 300g', 'description' => 'Creatina pura para força e desempenho.', 'price' => 89.90, 'stock' => 80],
            ['category' => 'Creatina e aminoácidos', 'name' => 'Creatina Monohidratada 150g', 'description' => 'Versão menor, ideal para começar.', 'price' => 54.90, 'stock' => 50],
            ['category' => 'Creatina e aminoácidos', 'name' => 'Creatina Creapure 250g', 'description' => 'Creatina de alta pureza.', 'price' => 129.90, 'stock' => 20],
            ['category' => 'Creatina e aminoácidos', 'name' => 'BCAA 2:1:1 (120 cápsulas)', 'description' => 'Aminoácidos de cadeia ramificada para recuperação.', 'price' => 64.90, 'stock' => 45],
            ['category' => 'Creatina e aminoácidos', 'name' => 'Glutamina 300g', 'description' => 'Aminoácido para recuperação muscular.', 'price' => 79.90, 'stock' => 30],
            ['category' => 'Creatina e aminoácidos', 'name' => 'EAA 300g', 'description' => 'Todos os aminoácidos essenciais em pó.', 'price' => 94.90, 'stock' => 28],
            ['category' => 'Creatina e aminoácidos', 'name' => 'Beta-Alanina 200g', 'description' => 'Ajuda a retardar a fadiga em treinos longos.', 'price' => 69.90, 'stock' => 33],
            ['category' => 'Creatina e aminoácidos', 'name' => 'Arginina (120 cápsulas)', 'description' => 'Aminoácido precursor do óxido nítrico.', 'price' => 49.90, 'stock' => 10, 'active' => false],

            // Pré-treino e energia
            ['category' => 'Pré-treino e energia', 'name' => 'Pré-Treino Frutas Vermelhas 300g', 'description' => 'Fórmula com cafeína e beta-alanina para foco e energia.', 'price' => 99.90, 'stock' => 38],
            ['category' => 'Pré-treino e energia', 'name' => 'Pré-Treino Sem Estimulante 300g', 'description' => 'Para quem treina à noite e quer evitar cafeína.', 'price' => 109.90, 'stock' => 14],
            ['category' => 'Pré-treino e energia', 'name' => 'Cafeína 200mg (60 cápsulas)', 'description' => 'Cafeína em cápsulas, dose individual.', 'price' => 39.90, 'stock' => 70],
            ['category' => 'Pré-treino e energia', 'name' => 'Termogênico (60 cápsulas)', 'description' => 'Auxilia no gasto energético durante o treino.', 'price' => 79.90, 'stock' => 26],
            ['category' => 'Pré-treino e energia', 'name' => 'Energy Gel 30g', 'description' => 'Gel de carboidrato de rápida absorção.', 'price' => 9.90, 'stock' => 150],
            ['category' => 'Pré-treino e energia', 'name' => 'Guaraná em Pó 100g', 'description' => 'Fonte natural de energia.', 'price' => 24.90, 'stock' => 55],
            ['category' => 'Pré-treino e energia', 'name' => 'Taurina (120 cápsulas)', 'description' => 'Aminoácido que contribui para o desempenho.', 'price' => 44.90, 'stock' => 40],

            // Acessórios
            ['category' => 'Acessórios', 'name' => 'Coqueteleira 600ml', 'description' => 'Coqueteleira com tampa rosqueável e misturador.', 'price' => 29.90, 'stock' => 120],
            ['category' => 'Acessórios', 'name' => 'Coqueteleira Inox 750ml', 'description' => 'Aço inoxidável, mantém a temperatura.', 'price' => 59.90, 'stock' => 45],
            ['category' => 'Acessórios', 'name' => 'Garrafa Térmica 1L', 'description' => 'Garrafa de aço inox, mantém a bebida gelada por horas.', 'price' => 69.90, 'stock' => 30],
            ['category' => 'Acessórios', 'name' => 'Luva de Treino', 'description' => 'Luva com proteção na palma para musculação.', 'price' => 49.90, 'stock' => 40],
            ['category' => 'Acessórios', 'name' => 'Cinto de Musculação', 'description' => 'Cinto de couro para apoio lombar em cargas altas.', 'price' => 129.90, 'stock' => 17],
            ['category' => 'Acessórios', 'name' => 'Straps de Levantamento', 'description' => 'Fitas para reforçar a pegada em levantamentos.', 'price' => 39.90, 'stock' => 55],
            ['category' => 'Acessórios', 'name' => 'Munhequeira', 'description' => 'Suporte para o punho, par.', 'price' => 34.90, 'stock' => 60],
            ['category' => 'Acessórios', 'name' => 'Dosador de Suplementos', 'description' => 'Porta-doses com 3 compartimentos.', 'price' => 14.90, 'stock' => 90],
            ['category' => 'Acessórios', 'name' => 'Bolsa Esportiva', 'description' => 'Bolsa com compartimento para tênis.', 'price' => 89.90, 'stock' => 19],
        ];

        // Nome do produto -> arquivo em database/seeders/images/
        $images = [
            'Whey Protein Concentrado 900g' => 'whey-concentrado-900g.jpg',
            'Whey Protein Isolado 900g' => 'whey-isolado-900g.jpg',
            'Whey Protein Concentrado 1,8kg' => 'whey-concentrado-1-8kg.jpg',
            'Caseína 900g' => 'caseina-900g.jpg',
            'Albumina 500g' => 'albumina-500g.jpg',
            'Proteína Vegetal 600g' => 'proteina-vegetal-600g.jpg',
            'Hipercalórico 3kg' => 'hipercalorico-3kg.jpg',
            'Barra de Proteína (caixa com 12)' => 'barra-proteina.jpg',
            'Creatina Monohidratada 300g' => 'creatina-300g.jpg',
            'Creatina Monohidratada 150g' => 'creatina-150g.jpg',
            'Creatina Creapure 250g' => 'creatina-creapure-250g.jpg',
            'BCAA 2:1:1 (120 cápsulas)' => 'bcaa.jpg',
            'Glutamina 300g' => 'glutamina-300g.jpg',
            'EAA 300g' => 'eaa-300g.jpg',
            'Beta-Alanina 200g' => 'beta-alanina.jpg',
            'Arginina (120 cápsulas)' => 'arginina.jpg',
            'Pré-Treino Frutas Vermelhas 300g' => 'pre-treino-frutas-vermelhas.jpg',
            'Pré-Treino Sem Estimulante 300g' => 'pre-treino-sem-estimulante.jpg',
            'Cafeína 200mg (60 cápsulas)' => 'cafeina.jpg',
            'Termogênico (60 cápsulas)' => 'termogenico.jpg',
            'Energy Gel 30g' => 'energy-gel.jpg',
            'Guaraná em Pó 100g' => 'guarana.jpg',
            'Taurina (120 cápsulas)' => 'taurina.jpg',
            'Coqueteleira 600ml' => 'coqueteleira-600ml.jpg',
            'Coqueteleira Inox 750ml' => 'coqueteleira-inox-750ml.jpg',
            'Garrafa Térmica 1L' => 'garrafa-termica-1l.jpg',
            'Luva de Treino' => 'luva-de-treino.jpg',
            'Cinto de Musculação' => 'cinto-de-musculacao.jpg',
            'Straps de Levantamento' => 'straps-de-levantamento.jpg',
            'Munhequeira' => 'munhequeira.jpg',
            'Dosador de Suplementos' => 'dosador-de-suplementos.jpg',
            'Bolsa Esportiva' => 'bolsa-esportiva.jpg',
        ];

        foreach ($products as $item) {
            $product = Product::create([
                'category_id' => $category_ids[$item['category']],
                'name'        => $item['name'],
                'description' => $item['description'],
                'price'       => $item['price'],
                'stock'       => $item['stock'],
                'active'      => $item['active'] ?? true,
            ]);

            $this->attachImage($product, $images[$item['name']] ?? null);
        }
    }

    // Copia a imagem para o disco "public" (storage/app/public/products) e grava o caminho em image_url.
    private function attachImage(Product $product, ?string $file): void
    {
        if (! $file) {
            return;
        }

        $source = database_path('seeders/images/' . $file);

        if (! file_exists($source)) {
            return;
        }

        $path = 'products/' . $file;
        Storage::disk('public')->put($path, file_get_contents($source));
        $product->update(['image_url' => $path]);
    }
}
