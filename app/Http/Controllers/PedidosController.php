<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;

class PedidosController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::all();
        return response()->json($pedidos);
    }

    public function show($id)
    {
        $pedido = Pedido::find($id);
        return response()->json($pedido);
    }

    public function store(Request $request)
    {
        $pedido = new Pedido;
        $pedido->fill($request->all());
        $pedido->save();

        return response()->json($pedido, 201);
    }

    public function update(Request $request, $id)
    {
        $pedido = Pedido::find($id);
        $pedido->fill($request->all());
        $pedido->save();

        return response()->json($pedido);
    }

    public function destroy($id)
    {
        $pedido = Pedido::find($id);
        $pedido->delete();

        return response()->json(null, 204);
    }
}