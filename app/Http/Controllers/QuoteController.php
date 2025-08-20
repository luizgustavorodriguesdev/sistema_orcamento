<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str; // Importante para gerar o hash único

class QuoteController extends Controller
{
    /**
     * Mostra uma lista de todos os orçamentos.
     */
    public function index(): Response
    {
        // Busca todos os orçamentos, carregando o nome do vendedor (usuário) associado a cada um.
        // O 'with('user')' evita o problema de N+1 queries, tornando a busca mais eficiente.
        $quotes = Quote::with('user')->latest()->paginate(10);

        // Renderiza o componente Vue 'Quotes/Index' e passa os orçamentos para ele.
        return Inertia::render('Quotes/Index', [
            'quotes' => $quotes,
        ]);
    }

    /**
     * Mostra o formulário para criar um novo orçamento.
     */
    public function create(): Response
    {
        // Para criar um orçamento, precisamos da lista de todos os produtos disponíveis.
        $products = Product::orderBy('name')->get();

        // Renderiza o componente Vue 'Quotes/Create' e passa a lista de produtos.
        return Inertia::render('Quotes/Create', [
            'products' => $products,
        ]);
    }

    /**
     * Salva um novo orçamento no banco de dados.
     * Esta função será a mais complexa.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validação dos dados recebidos do formulário Vue.
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_contact' => 'required|string|max:255',
            'payment_terms' => 'nullable|string',
            'delivery_info' => 'nullable|string',
            'items' => 'required|array|min:1', // Garante que pelo menos um item foi adicionado.
            'items.*.product_id' => 'required|exists:products,id', // Valida cada item do orçamento.
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // 2. Cálculo do valor total do orçamento.
        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $totalAmount += $product->price * $item['quantity'];
        }

        // 3. Criação do orçamento no banco.
        $quote = Quote::create([
            'client_name' => $validated['client_name'],
            'client_contact' => $validated['client_contact'],
            'payment_terms' => $validated['payment_terms'],
            'delivery_info' => $validated['delivery_info'],
            'total_amount' => $totalAmount,
            'user_id' => Auth::id(), // Associa o orçamento ao vendedor logado.
            'unique_hash' => Str::random(16), // Gera um hash aleatório para o link público.
            'status' => 'Pendente',
        ]);

        // 4. Anexar os produtos ao orçamento na tabela pivot 'quote_product'.
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $quote->products()->attach($item['product_id'], [
                'quantity' => $item['quantity'],
                'unit_price' => $product->price, // Salva o preço do produto no momento da cotação.
            ]);
        }

        // 5. Redireciona para a lista de orçamentos com uma mensagem de sucesso.
        return redirect()->route('quotes.index')->with('success', 'Orçamento criado com sucesso!');
    }

    /**
     * Mostra a página pública de um orçamento para o cliente.
     * Graças ao "Route Model Binding", o Laravel automaticamente encontra o orçamento
     * pelo 'unique_hash' e o injeta na função como a variável $quote.
     */
    public function showPublic(Quote $quote): Response
    {
        // Para exibir o orçamento completo, precisamos carregar os produtos associados
        // e também os dados do vendedor (usuário) que o criou.
        // Usamos load() para carregar esses relacionamentos no modelo já existente.
        $quote->load('products', 'user');

        // Renderiza o componente Vue 'Quotes/PublicShow' e passa o objeto
        // completo do orçamento (com produtos e vendedor) como uma prop.
        return Inertia::render('Quotes/PublicShow', [
            'quote' => $quote
        ]);
    }
}
