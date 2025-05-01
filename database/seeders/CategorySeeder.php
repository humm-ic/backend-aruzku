<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder {
    /**
    * Run the database seeds.
    */

    public function run(): void {
        // Expense categories
        $expenseCategories = [
            [ 'name' => 'Makanan', 'type' => 'expense', 'icon' => 'food' ],
            [ 'name' => 'Transportasi', 'type' => 'expense', 'icon' => 'transport' ],
            [ 'name' => 'Belanja', 'type' => 'expense', 'icon' => 'shopping' ],
            [ 'name' => 'Hiburan', 'type' => 'expense', 'icon' => 'entertainment' ],
            [ 'name' => 'Kesehatan', 'type' => 'expense', 'icon' => 'health' ],
            [ 'name' => 'Pendidikan', 'type' => 'expense', 'icon' => 'education' ],
            [ 'name' => 'Tagihan', 'type' => 'expense', 'icon' => 'bills' ],
            [ 'name' => 'Lainnya', 'type' => 'expense', 'icon' => 'other' ],
        ];

        // Income categories
        $incomeCategories = [
            [ 'name' => 'Pemasukan', 'type' => 'income', 'icon' => 'income' ],
            
        ];

        // Insert all categories
        foreach ( array_merge( $expenseCategories, $incomeCategories ) as $category ) {
            Category::create( $category );
        }
    }
}
