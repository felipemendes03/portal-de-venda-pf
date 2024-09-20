<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UsuariosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $menus = [
            [
                'rota' => 'dashboard',
                'nome' => 'Dashboard',
                'permissoes' => ['dashboard']
            ],
            [
                'rota' => 'produtos',
                'nome' => 'Produtos',
                'permissoes' => ['produtos']
            ],
            [
                'rota' => 'pedidos',
                'nome' => 'Pedidos',
                'permissoes' => ['pedidos']
            ],
            [
                'rota' => 'cozinha',
                'nome' => 'Cozinha',
                'permissoes' => ['cozinha']
            ],
            [
                'rota' => 'entrega',
                'nome' => 'Entrega',
                'permissoes' => ['entrega']
            ],
            [
                'rota' => 'security.users.get',
                'nome' => 'Usuários',
                'permissoes' => ['usuarios']
            ],
            [
                'rota' => 'security.profiles.get',
                'nome' => 'Perfis',
                'permissoes' => ['usuarios']
            ],
            [
                'rota' => 'cliente',
                'nome' => 'Clientes',
                'permissoes' => ['cadastro.cliente']
            ],
        ];
        $menusResponse = [];
        $usuariosService = new UsuariosService($request->user()->id_perfil);
        foreach ($menus as $menu) {
            if ($usuariosService->verificarPermissao($menu['permissoes'])) {
                unset($menu['permissoes']);
                $menusResponse[] = $menu;
            }
        }
        return response()->json($menusResponse);
    }

}
