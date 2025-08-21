<?php

namespace App\Http\Controllers;

use App\models\Client;
use App\Models\Product;
use App\Models\Quote;
use App\Models\PaymentMethod;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    /**
     * Mostra uma lista de todos os orçamentos.
     */
    public function index(): Response
    {
        // Busca todos os orçamentos, carregando o nome do vendedor (usuário) associado a cada um.
        // O 'with('user')' evita o problema de N+1 queries, tornando a busca mais eficiente.
        $quotes = Quote::with('user','client')->latest()->paginate(10);

        // Buscamos as configurações para usar o domínio da aplicação
        $settings = Setting::all()->pluck('value', 'key');

        // Renderiza o componente Vue 'Quotes/Index' e passa os orçamentos para ele.
        return Inertia::render('Quotes/Index', [
            'quotes' => $quotes,
            'settings'=> $settings
        ]);
    }

    /**
     * Mostra o formulário para criar um novo orçamento.
     */
    public function create(): Response
    {
        // Busca no banco de dados produtos,clientes e métodos de pagamento.
        $products = Product::orderBy('name')->get();
        $clients = Client::orderBy('name')->get(); 
        $paymentMethods = PaymentMethod::orderBy('name')->get();

        // Renderiza o componente Vue e passa ambas as listas.
        return Inertia::render('Quotes/Create', [
            'products' => $products,
            'clients' => $clients,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Salva um novo orçamento na base de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validação dos dados
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id', // Alterado de client_name para client_id
            'payment_terms' => 'nullable|string',
            'delivery_info' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
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
            'client_id' => $validated['client_id'], // Alterado para usar o ID do cliente
            'payment_terms' => $validated['payment_terms'],
            'delivery_info' => $validated['delivery_info'],
            'total_amount' => $totalAmount,
            'user_id' => Auth::id(),
            'unique_hash' => Str::random(16),
            'status' => 'Pendente',
        ]);

        // 4. Anexar os produtos ao orçamento
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $quote->products()->attach($item['product_id'], [
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
            ]);
        }

        // 5. Redireciona para a lista de orçamentos
        return redirect()->route('quotes.index')->with('success', 'Orçamento criado com sucesso!');
    }

    /**
     * Mostra a página pública de um orçamento para o cliente.
     * Graças ao "Route Model Binding", o Laravel automaticamente encontra o orçamento
     * pelo 'unique_hash' e o injeta na função como a variável $quote.
     */
    /**
     * Mostra a página pública de um orçamento para o cliente.
     */
    public function showPublic(Quote $quote): Response
    {
        // Carrega os relacionamentos necessários no orçamento.
        $quote->load('products', 'user', 'client');

        // Busca todas as configurações e transforma-as num formato fácil de usar no Vue.
        $settings = Setting::all()->pluck('value', 'key');

        // Renderiza o componente Vue e passa tanto o orçamento quanto as configurações.
        return Inertia::render('Quotes/PublicShow', [
            'quote' => $quote,
            'settings' => $settings, // Adicione esta linha
        ]);
    }

    /**
     * Mostra o formulário para editar um orçamento existente.
     */
    public function edit(Quote $quote): Response
    {
        // Carrega os produtos já associados a este orçamento
        $quote->load('products');

        return Inertia::render('Quotes/Edit', [
            'quote' => $quote,
            'products' => Product::orderBy('name')->get(),
            'clients' => Client::orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Atualiza um orçamento existente na base de dados.
     */
    public function update(Request $request, Quote $quote): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'payment_terms' => 'nullable|string',
            'delivery_info' => 'nullable|string',
            'status' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Usar uma transação para garantir a integridade dos dados
        DB::transaction(function () use ($validated, $quote) {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $totalAmount += $product->price * $item['quantity'];
            }

            $quote->update([
                'client_id' => $validated['client_id'],
                'payment_terms' => $validated['payment_terms'],
                'delivery_info' => $validated['delivery_info'],
                'status' => $validated['status'],
                'total_amount' => $totalAmount,
            ]);

            // Sincroniza os produtos. O método sync remove os antigos e adiciona os novos.
            $syncData = [];
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $syncData[$item['product_id']] = [
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                ];
            }
            $quote->products()->sync($syncData);
        });

        return redirect()->route('quotes.index')->with('success', 'Orçamento atualizado com sucesso!');
    }
}
