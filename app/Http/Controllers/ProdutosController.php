<?php

namespace App\Http\Controllers;

use App\Models\Produtos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProdutosController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        Produtos::create($request->all());
        return response()->json(["message"=>"Produto criado com sucesso!"],201); 
    }

    public function list(Request $request): JsonResponse
    {
        return response()->json(Produtos::all()); 
    }

    public function update(Request $request): JsonResponse
    {
        $produto = Produtos::get($request['id']);
        $produto->update($request->all());
        return response()->json(["message"=>"Produto alterado com sucesso!"]); 
    }
}
