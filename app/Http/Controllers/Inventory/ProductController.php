<?php

namespace App\Http\Controllers\Inventory;

use App\Domain\Inventory\Actions\DeleteProduct;
use App\Domain\Inventory\Actions\SaveProduct;
use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Queries\ProductDetailQuery;
use App\Domain\Inventory\Queries\ProductFormOptionsQuery;
use App\Domain\Inventory\Queries\ProductListQuery;
use App\Domain\Organization\Support\Access;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\SaveProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request, ProductListQuery $query): Response
    {
        return Inertia::render('Products/Index', [
            'products' => $query->paginate($request->only(['name', 'code', 'type', 'unit_id'])),
            'canCreate' => Access::manageableUnitIds() !== [],
        ]);
    }

    public function create(ProductFormOptionsQuery $options): Response
    {
        abort_if(Access::manageableUnitIds() === [], 403, 'Anda tidak punya akses menambah barang.');

        return Inertia::render('Products/Create', $options->get());
    }

    public function store(SaveProductRequest $request, SaveProduct $save): RedirectResponse
    {
        $product = $save->handle($request->toData());

        return redirect()->route('products.show', $product)->with('success', "Barang {$product->name} disimpan");
    }

    public function show(Product $product, ProductDetailQuery $query): Response
    {
        Access::authorizeView($product->unit_id);

        return Inertia::render('Products/Detail', $query->get($product));
    }

    public function edit(Product $product, ProductFormOptionsQuery $options): Response
    {
        Access::authorizeManage($product->unit_id);

        return Inertia::render('Products/Edit', $options->get($product));
    }

    public function update(SaveProductRequest $request, Product $product, SaveProduct $save): RedirectResponse
    {
        $save->handle($request->toData(), $product);

        return redirect()->route('products.show', $product)->with('success', "Barang {$product->name} diperbarui");
    }

    public function destroy(Product $product, DeleteProduct $delete): RedirectResponse
    {
        $delete->handle($product);

        return redirect()->route('products.index')->with('success', "Barang {$product->name} dihapus");
    }
}
