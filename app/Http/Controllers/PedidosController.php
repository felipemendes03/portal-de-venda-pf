<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidosProduto;
use App\Models\Produtos;

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
        $itens = $request->itens;
        $itensMapeados = [];
        $valorTotal = 0;

        foreach ($itens as $item) {
            $produto = Produtos::find($item['id']);
            $itensMapeados[] = [
                "id_produto" => $produto->id,
                "qt_produto" => $item['quantidade'],
                "vl_produto" => $produto->valor,
                "vl_total" => $item['quantidade'] * $produto->valor
            ];
            $valorTotal += $itensMapeados[count($itensMapeados)-1]["vl_total"];
        }

        $pedido = new Pedido;
        $pedido->fill($request->all());
        $pedido->vl_total = $valorTotal;
        $pedido->tp_status = 'EM_PREPARO';
        $pedido->save();
        foreach ($itensMapeados as $item) {
            $item['id_pedido'] = $pedido->id;
            PedidosProduto::create($item);
        }

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