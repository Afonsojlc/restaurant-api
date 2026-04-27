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
        // 1. Criar Utilizadores
        $admin = User::create([
            'name' => 'Proprietario',
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

        // 2. Criar Categorias
        $entradas = Category::create(['name' => 'Entradas', 'description' => 'Para comecar bem']);
        $principais = Category::create(['name' => 'Pratos Principais', 'description' => 'O melhor do chef']);
        $sobremesas = Category::create(['name' => 'Sobremesas', 'description' => 'Doce final']);

        // 3. Criar Pratos associados às categorias
        $caldo = $entradas->dishes()->create(['name' => 'Caldo Verde', 'price' => 5.50, 'allergens' => 'lactose']);
        $rissois = $entradas->dishes()->create(['name' => 'Rissois de Camarao', 'price' => 7.00, 'allergens' => 'gluten, marisco']);
        
        $bacalhau = $principais->dishes()->create(['name' => 'Bacalhau a Bras', 'price' => 18.50, 'allergens' => 'ovos, gluten']);
        $bife = $principais->dishes()->create(['name' => 'Bife na Pedra', 'price' => 22.00, 'allergens' => '']);
        
        $mousse = $sobremesas->dishes()->create(['name' => 'Mousse de Chocolate', 'price' => 4.50, 'allergens' => 'ovos, lactose']);

        // 4. Criar uma Reserva de exemplo para a Maria
        $reserva = Reservation::create([
            'user_id' => $cliente->id,
            'reserved_at' => now()->addDays(3),
            'guests' => 2,
            'notes' => 'Mesa perto da janela, se possivel.',
            'status' => 'pending',
        ]);

        // 5. Associar pratos à reserva (2 caldos e 2 bacalhaus) usando a tabela intermédia (pivot)
        $reserva->dishes()->attach([
            $caldo->id => ['quantity' => 2],
            $bacalhau->id => ['quantity' => 2]
        ]);
    }
}