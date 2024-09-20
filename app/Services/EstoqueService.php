<?php

namespace App\Services;
use App\Models\Pedido;
use App\Models\Produtos;

class EstoqueService{

    public static function atualizarProdutosAtivos(){
        $estoqueCalc = Pedido::where('tp_status', '!=', 'CANCELADO')
                    ->join('pedidos_produtos', 'pedidos.id', '=', 'pedidos_produtos.id_pedido')
                    ->join('produtos', 'pedidos_produtos.id_produto', '=', 'produtos.id')
                    ->select('produtos.id', 'produtos.nome', 'produtos.estoque', \DB::raw('sum(pedidos_produtos.qt_produto) as total'))
                    ->groupBy('produtos.id', 'produtos.nome', 'produtos.estoque')
                    ->get();
        foreach($estoqueCalc as $item){
            $disponivel = $item->estoque - $item->total;
            if($disponivel <= 0){
                Produtos::where('id', $item->id)
                    ->update(['ativo' =>  'N']);
            }else{
                Produtos::where('id', $item->id)
                    ->update(['ativo' =>  'S']);
            }
        }
    }

    public static function obterEstoqueDisponivel($idProduto) : int {

        $produto = Produtos::where('id', $idProduto)->first();

        if($produto->ativo == 'N'){
            return 0;
        }

        $estoqueCalc = Pedido::where('tp_status', '!=', 'CANCELADO')
                    ->join('pedidos_produtos', 'pedidos.id', '=', 'pedidos_produtos.id_pedido')
                    ->join('produtos', 'pedidos_produtos.id_produto', '=', 'produtos.id')
                    ->select('produtos.id', 'produtos.nome', 'produtos.estoque', \DB::raw('sum(pedidos_produtos.qt_produto) as total'))
                    ->where('produtos.id', $idProduto)
                    ->where('produtos.ativo', 'S')
                    ->groupBy('produtos.id', 'produtos.nome', 'produtos.estoque')
                    ->first();
        
        if($estoqueCalc){
            return $estoqueCalc->estoque - $estoqueCalc->total;
        }
           
        return Produtos::where('id', $idProduto)->first()->estoque;
    }
}