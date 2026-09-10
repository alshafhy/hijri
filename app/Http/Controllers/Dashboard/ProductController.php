<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\UnitOfMeasure;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $this->authorize('product.view');
        $products = Product::with(['category', 'unit'])->latest()->paginate(20);
        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        $this->authorize('product.create');
        $categories = Category::all();
        $units      = UnitOfMeasure::all();
        return view('products.create', compact('categories', 'units'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $branchId = auth()->user()->branch_id;
        if (!$branchId) {
            return back()->withErrors(['branch_id' => 'No branch assigned to user.']);
        }

        $product = Product::create(array_merge($request->validated(), [
            'branch_id'  => $branchId,
            'created_by' => auth()->id(),
        ]));
        return redirect()
            ->route('dashboard.products.show', $product->id)
            ->with('success', 'تم إنشاء المنتج بنجاح.');
    }

    public function show(Product $product): View
    {
        $this->authorize('product.view');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $this->authorize('product.edit');
        $categories = Category::all();
        $units      = UnitOfMeasure::all();
        return view('products.edit', compact('product', 'categories', 'units'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update(array_merge($request->validated(), [
            'updated_by' => auth()->id(),
        ]));
        return redirect()
            ->route('dashboard.products.show', $product->id)
            ->with('success', 'تم تحديث المنتج بنجاح.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('product.delete');
        $product->delete();
        return redirect()
            ->route('dashboard.products.index')
            ->with('success', 'تم حذف المنتج بنجاح.');
    }

    public function adjustStock(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('product.adjust_stock');

        $validated = $request->validate([
            'type'      => 'required|string|in:set,add,subtract',
            'quantity' => 'required|integer|min:0',
            'notes'     => 'nullable|string|max:500',
        ]);

        $product->adjustStock(
            (int) $validated['quantity'],
            $validated['type'],
            $validated['notes'] ?? null
        );

        return back()->with('success', 'تم تعديل المخزون بنجاح.');
    }
}