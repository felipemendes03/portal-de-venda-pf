<?php

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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/api/produtos',[ProdutosController::class, 'create'])->name('produtos.create');
    Route::get('/api/produtos',[ProdutosController::class, 'list'])->name('produtos.list');
    Route::put('/api/produtos',[ProdutosController::class, 'update'])->name('produtos.update');
});

require __DIR__.'/auth.php';
