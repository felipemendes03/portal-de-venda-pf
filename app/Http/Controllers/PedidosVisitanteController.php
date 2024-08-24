<?php

namespace App\Http\Controllers;

use App\Models\CadastroCliente;
use App\Models\Pedido;
use App\Models\PedidosProduto;
use App\Services\UsuariosDeClientesService;
use Illuminate\Http\Request;
use App\Models\Produtos;

class PedidosVisitanteController extends Controller
{

    public function index(Request $request)
    {
        $produtos = Produtos::where('ativo', 'S')
        ->orderBy('nome', 'asc')
        ->get();
        return response()->json([
            'produtos' => $produtos
        ]);
    }

    public function show($id)
    {
        $pedido = Pedido::find($id);
        $pedido['itens'] = PedidosProduto::where('id_pedido', $id)
            ->join('produtos', 'pedidos_produtos.id_produto', '=', 'produtos.id')
            ->select('pedidos_produtos.*', 'produtos.nome as nm_produto')->get();
        return response()->json($pedido);
    }

    public function getHistorico(Request $request)
    {
        $cadastroCliente = $this->getCadastroCliente($request->header('Authorization'));
        $pedidos = Pedido::select('pedidos.*', \DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as created_at_formatted"))
        ->where('id_cadastro_cliente', $cadastroCliente->id)
        ->orderBy('created_at', 'desc')
        ->get();

        foreach ($pedidos as $pedido) {
            $pedido['itens'] = PedidosProduto::where('id_pedido', $pedido->id)
                ->join('produtos', 'pedidos_produtos.id_produto', '=', 'produtos.id')->select('pedidos_produtos.*', 'produtos.nome as nm_produto')->get();
        }
        
        return response()->json(["historico" => $pedidos]);
    }

    public function store(Request $request)
    {
        $cadastroCliente = $this->getCadastroCliente($request->header('Authorization'));
        $pedidoRequest = $request->all()['pedido'];
        $itens = $pedidoRequest['itens'];
        $itensMapeados = [];
        $valorTotal = 0;
        $produtosInvativos = [];

        foreach ($itens as $item) {
            $produto = Produtos::find($item['id']);
            $itensMapeados[] = [
                "id_produto" => $produto->id,
                "qt_produto" => $item['quantidade'],
                "vl_produto" => $produto->valor,
                "vl_total" => $item['quantidade'] * $produto->valor
            ];
            if($produto->ativo == 'N'){
                $produtosInvativos[] = $produto->nome;
            }
            $valorTotal += $itensMapeados[count($itensMapeados)-1]["vl_total"];
        }
        
        if(count($produtosInvativos) > 0){
            return response()->json([
                "message" => "Enquanto você fazia o pedido, os seguintes itens não estão mais disponíveis:",
                "itens" => $produtosInvativos
            ], 422);
        }

        $pedido = new Pedido();
        $pedido->fill($pedidoRequest);
        $pedido->vl_total = $valorTotal;
        $pedido->tp_status = 'PENDENTE_PAGAMENTO';
        if($cadastroCliente){
            $pedido->id_cadastro_cliente = $cadastroCliente->id;
            $pedido->nm_cliente = $cadastroCliente->nome;
        }else{
            $pedido->nm_cliente = $pedidoRequest['nome'];
        }
        $pedido->tp_pagamento = $pedidoRequest['formaPagamento'];
        $pedido->ds_observacao = $pedidoRequest['observacao'];
        $pedido->save();
        foreach ($itensMapeados as $item) {
            $item['id_pedido'] = $pedido->id;
            PedidosProduto::create($item);
        }

        return response()->json($pedido, 201);
    }

    private function getCadastroCliente(String $jwt) : CadastroCliente|null{
        if(!$jwt){
            return null;
        }
        $jwt = str_replace('Bearer ', '', $jwt);
        $clienteId = UsuariosDeClientesService::validarJwt($jwt);
        return CadastroCliente::find($clienteId);
    }
   
}