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
        // 1. Start with parents only
        $query = Category::whereNull('parent_id')
            ->with(['creator', 'updater', 'children.creator', 'children.updater']);

        // 2. Search logic: Search parent OR children
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('children', function ($cq) use ($search) {
                      $cq->where('name', 'like', '%' . $search . '%')
                         ->orWhere('description', 'like', '%' . $search . '%');
                  });
            });
        }

        // 3. Paginate
        $categories = $query->orderBy('name')->paginate(10);

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
        $parents = Category::whereNull('parent_id')->select('id', 'name')->orderBy('name')->get();
        return Inertia::render('Admin/Categories/Create', [
            'parents' => $parents
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id,parent_id,NULL',
            'unit_group' => 'nullable|string|max:50',
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
        // Check if it's a parent category (has children)
        if ($category->children()->exists()) {
            $category->load(['children.products' => function ($query) {
                // Ensure we load creator/updater for the products in children
                $query->with(['creator', 'updater']);
            }]);

            // Aggregate products for stats only
            $allProducts = $category->children->flatMap(function ($child) {
                return $child->products;
            });
            
            // We do NOT override the relationship here, so the view can access children and their products
        } else {
             // It's a subcategory or standalone, load its own products
            $category->load(['products' => function ($query) {
                $query->with(['creator', 'updater']);
            }]);
            $allProducts = $category->products;
        }

        return Inertia::render('Admin/Categories/Show', [
            'category' => $category,
            'totalProducts' => $allProducts->count(),
            'totalStock' => $allProducts->sum('stock'),
            'totalValue' => $allProducts->sum(function ($product) {
                return $product->stock * $product->harga_beli;
            }),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $parents = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
            'parents' => $parents
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id,parent_id,NULL',
            'unit_group' => 'nullable|string|max:50',
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
