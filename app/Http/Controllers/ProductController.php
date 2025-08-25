<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category; 
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Mostra uma lista de todos os produtos.
     */
    public function index(): Response
    {
        // Carregamos o nome da categoria junto com cada produto para a listagem
        $products = Product::with('category')->latest()->paginate(10);

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }

    /**
     * Mostra o formulário para criar um novo produto.
     */
    public function create(): Response
    {
        return Inertia::render('Products/Create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Salva um novo produto na base de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'promotional_price' => 'nullable|numeric|min:0|lt:price', 
            'category_id' => 'nullable|exists:categories,id',
        ]);

        Product::create($request->all());

        return redirect()->route('products.index')
                         ->with('success', 'Produto criado com sucesso.');
    }

    /**
     * Mostra o formulário para editar um produto existente.
     */
    public function edit(Product $product): Response
    {
        $categories = Category::orderBy('name')->get();
        //dd($categories);
        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Atualiza um produto existente na base de dados.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'promotional_price' => 'nullable|numeric|min:0|lt:price', 
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $product->update($request->all());

        return redirect()->route('products.index')
                         ->with('success', 'Produto atualizado com sucesso.');
    }

    /**
     * Remove um produto da base de dados.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')
                         ->with('success', 'Produto apagado com sucesso.');
    }
}
