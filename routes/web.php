<?php

use App\Http\Controllers\PedidosController;
use App\Http\Controllers\ProdutosController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/produtos', function () {
    return Inertia::render('Produtos/Produtos');
})->middleware(['auth', 'verified'])->name('produtos');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pedidos', function () {return Inertia::render('Pedidos/Pedidos');})->name('pedidos');
    Route::get('/pedidos/historico', function () {return Inertia::render('Pedidos/PedidosHistorico');})->name('pedidos.historico');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/api/produtos', [ProdutosController::class, 'create'])->name('produtos.create');
    Route::get('/api/produtos', [ProdutosController::class, 'index'])->name('produtos.index');
    Route::put('/api/produtos/{id}', [ProdutosController::class, 'update'])->name('produtos.update');

    makeCrud(PedidosController::class, '/api/pedidos');
});

function makeCrud($controller, $basePath){
    $name = str_replace('/', '.', substr($basePath, 1));
    Route::get($basePath, [$controller, 'index'])->name($name.'.index');
    Route::get($basePath.'/{id}', [$controller, 'show'])->name($name.'.show');
    Route::post($basePath, [$controller, 'store'])->name($name.'.store');
    Route::put($basePath.'/{id}', [$controller, 'update'])->name($name.'.update');
    Route::delete($basePath.'/{id}', [$controller, 'destroy'])->name($name.'.destroy');
}

require __DIR__.'/auth.php';
