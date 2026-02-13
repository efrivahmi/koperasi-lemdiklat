<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Generate unique barcode for product
     * Format: 2XXXXXXXXXXX (13 digits - EAN-13 compatible)
     * Prefix "2" indicates internal/private label product
     */
    private function generateBarcode()
    {
        do {
            // BEST PRACTICE: Generate string angka digit per digit 
            // untuk menghindari limit Integer pada OS tertentu (Windows/32-bit).
            // Kita butuh 12 digit (Prefix '2' + 11 digit random) sebelum check digit
            $baseCode = '2';
            for ($i = 0; $i < 11; $i++) {
                $baseCode .= mt_rand(0, 9);
            }

            // Hitung Check Digit EAN-13
            $sum = 0;
            for ($i = 0; $i < 12; $i++) {
                $sum += (int)$baseCode[$i] * (($i % 2 === 0) ? 1 : 3);
            }
            $checkDigit = (10 - ($sum % 10)) % 10;
            
            // Gabungkan Base Code + Check Digit
            $barcode = $baseCode . $checkDigit;

        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'creator', 'updater']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%');
        }

        // --- PENTING: MENGGUNAKAN OLDEST() ---
        $products = $query->oldest()->paginate(10);

        // Check if we are in the Kasir route group
        if ($request->routeIs('kasir.*')) {
            return Inertia::render('Kasir/Products/Index', [
                'products' => $products,
                'categories' => Category::all(),
                'filters' => $request->only(['search']),
            ]);
        }

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();

        return Inertia::render('Admin/Products/Create', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'barcode' => 'nullable|string|unique:products,barcode',
            'auto_generate_barcode' => 'nullable|boolean',
        ]);

        // Set stock to 0 by default - stock management is done via Stock In menu
        $validated['stock'] = 0;

        // Auto-generate barcode if requested or if not provided
        if ($request->input('auto_generate_barcode', false) || empty($validated['barcode'])) {
            $validated['barcode'] = $this->generateBarcode();
        }

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

        $route = $request->routeIs('kasir.*') ? 'kasir.products.index' : 'products.index';
        return redirect()->route($route)
            ->with('success', 'Produk berhasil ditambahkan dengan barcode: ' . $validated['barcode'] . '. Kelola stok melalui menu "Barang Masuk".');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return Inertia::render('Admin/Products/Show', [
            'product' => $product->load([
                'category',
                'saleItems.creator',
                'saleItems.updater',
                'stockIns.creator',
                'stockIns.updater'
            ])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'stock' => 'required|integer|min:0',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
        ]);

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Hapus gambar jika ada
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Get stock monitoring data for real-time updates
     */
    public function stockMonitor()
    {
        $products = Product::with('category')
            ->where('stock', '<=', config('business.inventory.low_stock_threshold'))
            ->orderBy('stock', 'asc')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock,
                    'category' => [
                        'name' => $product->category->name ?? 'Tanpa Kategori'
                    ]
                ];
            });

        return response()->json($products);
    }

    /**
     * Show barcode generator page
     */
    public function barcodeGenerator(Request $request)
    {
        $query = Product::with('category');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%');
        }

        // Konsisten pakai oldest() juga di sini
        $products = $query->oldest()->paginate(20);

        return Inertia::render('Admin/Products/BarcodeGenerator', [
            'products' => $products,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Print barcode label for single product
     */
    public function printBarcode(Product $product)
    {
        $products = collect([$product->load('category')]);
        $quantity = 1;

        return view('barcode-labels', [
            'products' => $products,
            'quantity' => $quantity
        ]);
    }

    /**
     * Print barcode labels for selected products
     */
    public function printBarcodes(Request $request)
    {
        $productIds = $request->input('product_ids', []);
        $quantity = $request->input('quantity', 1); // How many labels per product

        $products = Product::with('category')
            ->whereIn('id', $productIds)
            ->get();

        return view('barcode-labels', [
            'products' => $products,
            'quantity' => $quantity
        ]);
    }

    /**
     * Generate new barcode (API endpoint for frontend)
     */
    public function generateBarcodeApi()
    {
        $barcode = $this->generateBarcode();

        return response()->json([
            'barcode' => $barcode,
            'message' => 'Barcode berhasil digenerate (EAN-13 format)'
        ]);
    }
}