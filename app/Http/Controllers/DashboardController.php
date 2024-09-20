<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\UsuariosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function __construct(Request $request)
    {
        $usrSrv = new UsuariosService($request->user()->id_perfil);
        if (!$usrSrv->verificarPermissao(['dashboard'])) {
            abort(403, 'Você não tem permissão para acessar essa página. [dashboard]');
        }
    }
    
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            [
                "vl_total_vendas" => Pedido::where('tp_status', '!=', 'CANCELADO')->sum('vl_total'),
                "nr_pedidos" => Pedido::where('tp_status', '!=', 'CANCELADO')->count(),
                "nr_aguardando_pagamento" => Pedido::where('tp_status', 'PENDENTE_PAGAMENTO')->count(),
                "nr_em_preparo" => Pedido::where('tp_status', 'EM_PREPARO')->count(),
                "nr_aguardando_retirada" => Pedido::where('tp_status', 'AGUARDANDO_RETIRADA')->count(),
                "nr_concluidos" => Pedido::where('tp_status', 'ENTREGUE')->count(),
                "nr_cancelados" => Pedido::where('tp_status', 'CANCELADO')->count(),
                "vl_pago_dinheiro" => Pedido::where('tp_pagamento', 'DINHEIRO')->where('tp_status', '!=', 'CANCELADO')->sum('vl_total'),
                "vl_pago_cartao" => Pedido::where('tp_pagamento', 'CARTAO')->where('tp_status', '!=', 'CANCELADO')->sum('vl_total'),
                "vl_pago_pix" => Pedido::where('tp_pagamento', 'PIX')->where('tp_status', '!=', 'CANCELADO')->sum('vl_total'),
                "vl_fiado" => Pedido::where('tp_pagamento', 'FIADO')->where('tp_status', '!=', 'CANCELADO')->sum('vl_total'),
                "produtos" => Pedido::where('tp_status', '!=', 'CANCELADO')
                    ->join('pedidos_produtos', 'pedidos.id', '=', 'pedidos_produtos.id_pedido')
                    ->join('produtos', 'pedidos_produtos.id_produto', '=', 'produtos.id')
                    ->select('produtos.id', 'produtos.nome', 'produtos.estoque', \DB::raw('sum(pedidos_produtos.qt_produto) as total'))
                    ->groupBy('produtos.id', 'produtos.nome', 'produtos.estoque')
                    ->get()
            ]
        ); 
    }

}
