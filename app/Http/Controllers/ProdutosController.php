<?php

namespace App\Http\Controllers;

use App\Models\Produtos;
use App\Services\EstoqueService;
use App\Services\UsuariosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProdutosController extends Controller
{
    public function __construct(Request $request)
    {
        $usrSrv = new UsuariosService($request->user()->id_perfil);
        if (!$usrSrv->verificarPermissao(['produtos','pedidos'])) {
            abort(403, "Você não tem permissão para acessar essa página. ['produtos','pedidos']");
        }
    }
    
    public function create(Request $request): JsonResponse
    {
        $usrSrv = new UsuariosService($request->user()->id_perfil);
        if (!$usrSrv->verificarPermissao(['produtos'])) {
            abort(403, "Você não tem permissão para acessar essa página. ['produtos']");
        }
        Produtos::create($request->all());
        return response()->json(["message"=>"Produto criado com sucesso!"],201); 
    }

    public function index(Request $request): JsonResponse
    {
        $orderBy = $request->input("orderBy");
        if(!$orderBy) $orderBy = 'id';

        $produtos = Produtos::orderBy($orderBy)->get();
        foreach ($produtos as $produto) {
            $produto->estoqueDisponivel = EstoqueService::obterEstoqueDisponivel($produto->id);
        }

        return response()->json($produtos, 200, [], JSON_NUMERIC_CHECK);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $usrSrv = new UsuariosService($request->user()->id_perfil);
        if (!$usrSrv->verificarPermissao(['produtos'])) {
            abort(403, "Você não tem permissão para acessar essa página. ['produtos']");
        }
        
        $produto = Produtos::find($id);
        $produto->update($request->all());
        return response()->json(["message"=>"Produto alterado com sucesso!"]); 
    }
}
