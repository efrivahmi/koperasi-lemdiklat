<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::with(['creator', 'updater']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $categories = $query->oldest()->paginate(10);

        if ($request->routeIs('kasir.*')) {
            return Inertia::render('Kasir/Categories/Index', [
                'categories' => $categories,
                'filters' => $request->only(['search'])
            ]);
        }

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Categories/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Category::create($validated);

        $route = $request->routeIs('kasir.*') ? 'kasir.categories.index' : 'categories.index';
        return redirect()->route($route)
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return Inertia::render('Admin/Categories/Show', [
            'category' => $category->load(['products.creator', 'products.updater'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        $route = $request->routeIs('kasir.*') ? 'kasir.categories.index' : 'categories.index';
        return redirect()->route($route)
            ->with('success', 'Kategori berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Category $category)
    {
        $category->delete();

        $route = $request->routeIs('kasir.*') ? 'kasir.categories.index' : 'categories.index';
        return redirect()->route($route)
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
