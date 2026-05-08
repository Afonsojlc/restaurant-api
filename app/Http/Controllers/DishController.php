<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use Illuminate\Http\Request;

class DishController extends Controller
{
    // GET /api/dishes?category_id=1&available=true&max_price=15 (PÚBLICO)
    public function index(Request $request)
    {
        $q = Dish::with('category'); // Carrega a categoria do prato para sabermos a que secção pertence

        // Filtro por Categoria
        if ($request->has('category_id')) {
            $q->where('category_id', $request->category_id);
        }

        // Filtro por Disponibilidade
        if ($request->has('available')) {
            $q->where('available', $request->boolean('available'));
        }

        // Filtro por Preço Máximo
        if ($request->has('max_price')) {
            $q->where('price', '<=', $request->max_price);
        }

        // Retorna com paginação (12 pratos por página)
        return response()->json($q->paginate(12));
    }

    // GET /api/dishes/{id} (PÚBLICO)
    public function show(Dish $dish)
    {
        return response()->json($dish->load('category'));
    }

    // POST /api/dishes - ADMIN
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'available' => 'boolean',
            'allergens' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $dish = Dish::create($data);
        
        return response()->json($dish->load('category'), 201);
    }

    // PUT /api/dishes/{id} - ADMIN
    public function update(Request $request, Dish $dish)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'available' => 'boolean',
            'allergens' => 'nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
        ]);

        $dish->update($data);
        
        return response()->json($dish->load('category'));
    }

    // DELETE /api/dishes/{id} - ADMIN
    public function destroy(Dish $dish)
    {
        $dish->delete();
        
        return response()->json(['message' => 'Prato removido.']);
    }
}