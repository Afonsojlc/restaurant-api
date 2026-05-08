<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // GET /api/categories (PÚBLICO)
    public function index()
    {
        return response()->json(
            Category::withCount('dishes')->where('active', true)->get()
        );
    }

    // GET /api/categories/{id} (PÚBLICO - inclui pratos disponíveis)
    public function show(Category $category)
    {
        return response()->json(
            $category->load(['dishes' => fn($q) => $q->where('available', true)])
        );
    }

    // POST /api/categories - ADMIN
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories',
            'active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        return response()->json(Category::create($data), 201);
    }

    // PUT /api/categories/{id} - ADMIN
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:100|unique:categories,name,' . $category->id,
            'active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $category->update($data);
        return response()->json($category);
    }

    // DELETE /api/categories/{id} - ADMIN
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Categoria removida.']);
    }
}