<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController; 
use App\Http\Controllers\QuoteController; 
use App\Http\Controllers\ClientController; 
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia; 


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rota pública para visualização do orçamento
Route::get('/orcamento/{quote:unique_hash}', [QuoteController::class, 'showPublic'])->name('quotes.public.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Nossas rotas de produtos
    // O método resource() cria todas as rotas necessárias para o CRUD de uma vez.
    Route::resource('products', ProductController::class);

    // rotas de orçamentos
    Route::resource('quotes', QuoteController::class);

    // Rota para usuarios
    Route::resource('users', UserController::class);

    // Rota Cliente
    Route::resource('clients', ClientController::class);

    // Rota Meios de Pagamento
    Route::resource('payment-methods', PaymentMethodController::class);

    // Rota Configurações
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');

    //Rota para categoria de produtos
    Route::resource('categories', CategoryController::class);
});

require __DIR__.'/auth.php';
