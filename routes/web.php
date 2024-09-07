<?php

use App\Http\Controllers\CadastroClienteController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\PedidosVisitanteController;
use App\Http\Controllers\ProdutosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Security\SecurityProfileContorller;
use App\Http\Controllers\Security\SecurityUserContorller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', []);
})->name('welcome');


Route::get('/visitante/pedido/{id}', function (Request $request) {
    return Inertia::render('Welcome', [
        'pedidoId' => $request->id
    ]);
});

Route::get('/meus-pedidos', function () {
    return Inertia::render('AreaCliente/HistoricoPedidos', [
        
    ]);
})->name('meus-pedidos');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('clientes', function(){
    return Inertia::render('Cliente/Cliente');
})->middleware(['auth', 'verified'])->name('cliente');


Route::get('/api/pedidos/visitante', [PedidosVisitanteController::class, 'index'])->name('api.pedidos.visitante.index');
Route::get('/api/pedidos/visitante/historico', [PedidosVisitanteController::class, 'getHistorico'])->name('api.pedidos.visitante.historico');
Route::get('/api/pedidos/visitante/{id}', [PedidosVisitanteController::class, 'show'])->name('api.pedidos.visitante.show');
Route::post('/api/pedidos/visitante', [PedidosVisitanteController::class, 'store'])->name('api.pedidos.visitante.store');


Route::post('/api/clientes/login', [CadastroClienteController::class, 'login'])->name('api.clientes.login');
Route::post('/api/clientes/verificar', [CadastroClienteController::class, 'verificarSeExiste'])->name('api.clientes.verificar-castrado');
Route::get('/api/clientes', [CadastroClienteController::class, 'show'])->name('api.clientes.show');
Route::post('/api/clientes', [CadastroClienteController::class, 'store'])->name('api.clientes.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/api/dashboard', [DashboardController::class, 'index'])->name('api.dashboard.index');

    Route::get('/produtos', function () {return Inertia::render('Produtos/Produtos');})->name('produtos');
    Route::get('/pedidos', function () {return Inertia::render('Pedidos/Pedidos');})->name('pedidos');
    Route::get('/pedidos/historico', function () {return Inertia::render('Pedidos/PedidosHistorico');})->name('pedidos.historico');
    Route::get('/cozinha', function () {return Inertia::render('Cozinha/Pedidos');})->name('cozinha');
    Route::get('/entrega', function () {return Inertia::render('Entrega/Pedidos');})->name('entrega');
    
    Route::get('/security/users', [SecurityUserContorller::class, 'getUsers'])->name('security.users.get');
    Route::post('/security/users', [SecurityUserContorller::class, 'saveUser'])->name('security.users.post');
    Route::delete('/security/users/{id}', [SecurityUserContorller::class, 'deleteUser'])->name('security.users.delete');
    
    Route::get('/security/profiles', [SecurityProfileContorller::class, 'getProfiles'])->name('security.profiles.get');
    Route::post('/security/profiles', [SecurityProfileContorller::class, 'saveProfile'])->name('security.profiles.post');
    Route::delete('/security/profiles/{id}', [SecurityProfileContorller::class, 'deleteProfile'])->name('security.profiles.delete');
    Route::get('/security/profiles/{idProfile}/permission', [SecurityProfileContorller::class, 'getProfilePermissions'])->name('security.profiles.permissions.get');
    
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/api/produtos', [ProdutosController::class, 'create'])->name('produtos.create');
    Route::get('/api/produtos', [ProdutosController::class, 'index'])->name('produtos.index');
    Route::put('/api/produtos/{id}', [ProdutosController::class, 'update'])->name('produtos.update');

    Route::get('/api/menu', [MenuController::class, 'index'])->name('menu.index');
    
    
    makeCrud(PedidosController::class, '/api/pedidos');
    makeCrud(ClientesController::class, '/api/admin/clientes');
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
