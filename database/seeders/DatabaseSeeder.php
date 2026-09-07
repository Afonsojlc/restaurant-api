<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Users (Restaurant Owner Admin & Customer)
        $admin = User::create([
            'name' => 'Restaurant Owner',
            'email' => 'admin@restaurante.pt',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '912345678',
        ]);

        $cliente = User::create([
            'name' => 'Maria Oliveira',
            'email' => 'maria@email.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'phone' => '961234567',
        ]);

        // 2. Seed Menu Categories
        $entradas = Category::create(['name' => 'Starters', 'description' => 'To start your dining experience']);
        $principais = Category::create(['name' => 'Main Courses', 'description' => 'Chef specials and signature dishes']);
        $sobremesas = Category::create(['name' => 'Desserts', 'description' => 'Sweet delicacies']);

        // 3. Seed Menu Dishes linked to Categories
        $caldo = $entradas->dishes()->create(['name' => 'Caldo Verde', 'price' => 5.50, 'allergens' => 'lactose']);
        $rissois = $entradas->dishes()->create(['name' => 'Shrimp Patties (Rissóis)', 'price' => 7.00, 'allergens' => 'gluten, shellfish']);

        $bacalhau = $principais->dishes()->create(['name' => 'Bacalhau à Brás', 'price' => 18.50, 'allergens' => 'eggs, gluten']);
        $bife = $principais->dishes()->create(['name' => 'Stone Steak', 'price' => 22.00, 'allergens' => '']);

        $mousse = $sobremesas->dishes()->create(['name' => 'Chocolate Mousse', 'price' => 4.50, 'allergens' => 'eggs, lactose']);

        // 4. Seed an initial table reservation for Maria
        $reserva = Reservation::create([
            'user_id' => $cliente->id,
            'reserved_at' => now()->addDays(3),
            'guests' => 2,
            'notes' => 'Table by the window if available, please.',
            'status' => 'pending',
        ]);

        // 5. Attach dishes with quantities using Many-to-Many pivot table (reservation_dish)
        $reserva->dishes()->attach([
            $caldo->id => ['quantity' => 2],
            $bacalhau->id => ['quantity' => 2]
        ]);
    }
}
