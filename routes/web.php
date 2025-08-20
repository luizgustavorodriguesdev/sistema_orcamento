<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController; // Adicione esta linha
use App\Http\Controllers\QuoteController; // Certifique-se de que QuoteController está importado corretamente
use App\Http\Controllers\ClientController; // Adicione esta linha
use App\Http\Controllers\UserController; // Adicione esta linha
use Illuminate\Support\Facades\Route;
use Inertia\Inertia; // Adicione esta linha no topo do ficheiro

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
    Route::resource('clients', ClientController::class); // Adicione esta linha
});

require __DIR__.'/auth.php';
