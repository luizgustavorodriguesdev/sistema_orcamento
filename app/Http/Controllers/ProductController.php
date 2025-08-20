<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia; // Importe a classe Inertia
use Inertia\Response; // Importe a classe Response

class ProductController extends Controller
{
    /**
     * Mostra uma lista de todos os produtos.
     */
    public function index(): Response
    {
        // A lógica de busca continua a mesma
        $products = Product::latest()->paginate(10);

        // Em vez de view(), usamos Inertia::render()
        // O primeiro argumento é o nome do componente Vue que será carregado
        // O segundo argumento é um array com os dados (props) que passamos para o componente
        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }

    /**
     * Mostra o formulário para criar um novo produto.
     */
    public function create(): Response
    {
        return Inertia::render('Products/Create');
    }

    /**
     * Salva um novo produto no banco de dados.
     * As funções de store, update e destroy não mudam, pois usam redirects.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
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
        return Inertia::render('Products/Edit', [
            'product' => $product,
        ]);
    }

    /**
     * Atualiza um produto existente no banco de dados.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        $product->update($request->all());

        return redirect()->route('products.index')
                         ->with('success', 'Produto atualizado com sucesso.');
    }

    /**
     * Remove um produto do banco de dados.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')
                         ->with('success', 'Produto deletado com sucesso.');
    }
}
