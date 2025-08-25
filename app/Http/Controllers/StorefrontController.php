<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class StorefrontController extends Controller
{
    public function index(): Response
    {
        $products = Product::with(['images' => function ($query) {
            $query->where('is_main', true);
        }])->latest()->paginate(12);

        $categories = Category::orderBy('name')->get();
        $settings = Setting::all()->pluck('value', 'key');

        return Inertia::render('Storefront/Index', [
            'products' => $products,
            'categories' => $categories,
            'settings' => $settings,
        ]);
    }

    /**
     * Exibe a página do carrinho de orçamento.
     */
    public function cart(): Response
    {
        // Precisamos das categorias e configurações para o layout da página.
        $categories = Category::orderBy('name')->get();
        $settings = Setting::all()->pluck('value', 'key');
        // Também precisamos de todos os produtos com as suas escalas de preços para calcular o total.
        $products = Product::with('priceTiers')->get();

        return Inertia::render('Storefront/Cart', [
            'categories' => $categories,
            'settings' => $settings,
            'products' => $products,
        ]);
    }

    /**
     * Guarda o orçamento criado pelo cliente.
     */
    public function storeQuote(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_contact' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Encontra ou cria um novo cliente com base no contacto.
        $client = Client::firstOrCreate(
            ['contact_main' => $validated['client_contact']],
            ['name' => $validated['client_name']]
        );

        $totalAmount = 0;
        $itemsToAttach = [];

        foreach ($validated['items'] as $item) {
            $product = Product::with('priceTiers')->find($item['id']);
            $quantity = $item['quantity'];
            $unitPrice = $this->getPriceForQuantity($product, $quantity);

            $totalAmount += $unitPrice * $quantity;
            $itemsToAttach[$item['id']] = [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ];
        }

        $quote = Quote::create([
            'client_id' => $client->id,
            'total_amount' => $totalAmount,
            'user_id' => null, // Orçamento criado pelo cliente, sem vendedor.
            'unique_hash' => Str::random(16),
            'status' => 'Pendente',
        ]);

        $quote->products()->attach($itemsToAttach);

        // Redireciona o cliente para a página pública do seu novo orçamento.
        return redirect()->route('quotes.public.show', $quote);
    }

    /**
     * Função auxiliar para encontrar o preço correto com base na quantidade.
     */
    private function getPriceForQuantity(Product $product, int $quantity): float
    {
        $tier = $product->priceTiers()
                        ->where('min_quantity', '<=', $quantity)
                        ->orderBy('min_quantity', 'desc')
                        ->first();
        return $tier ? $tier->price : $product->price;
    }
}
