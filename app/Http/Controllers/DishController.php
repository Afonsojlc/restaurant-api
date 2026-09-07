<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use Illuminate\Http\Request;

class DishController extends Controller
{
    // GET /api/dishes?category_id=1&available=true&max_price=15 (PUBLIC)
    public function index(Request $request)
    {
        $q = Dish::with('category');

        // Filter by Category
        if ($request->has('category_id')) {
            $q->where('category_id', $request->category_id);
        }

        // Filter by Availability
        if ($request->has('available')) {
            $q->where('available', $request->boolean('available'));
        }

        // Filter by Maximum Price
        if ($request->has('max_price')) {
            $q->where('price', '<=', $request->max_price);
        }

        // Paginated results (12 dishes per page)
        return response()->json($q->paginate(12));
    }

    // GET /api/dishes/{id} (PUBLIC)
    public function show(Dish $dish)
    {
        return response()->json($dish->load('category'));
    }

    // POST /api/dishes (ADMIN ONLY)
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

    // PUT /api/dishes/{id} (ADMIN ONLY)
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

    // DELETE /api/dishes/{id} (ADMIN ONLY)
    public function destroy(Dish $dish)
    {
        $dish->delete();

        return response()->json(['message' => 'Dish removed successfully.']);
    }
}
