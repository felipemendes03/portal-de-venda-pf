<?php

namespace App\Http\Controllers;

use App\Services\EnviarMensagemWhatsApp;
use App\Services\EstoqueService;
use App\Services\UsuariosService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidosProduto;
use App\Models\Produtos;

class PedidosController extends Controller
{

    public function __construct(Request $request)
    {
        $usrSrv = new UsuariosService($request->user()->id_perfil);
        if (!$usrSrv->verificarPermissao(['pedidos', 'cozinha', 'entrega'])) {
            abort(403, "Você não tem permissão para acessar essa página. ['pedidos', 'cozinha', 'entrega']");
        }
    }

    public function index(Request $request)
    {
        $status = $request->input("status");
        $comItens = $request->input("comItens");
        $orderBy = $request->input("orderBy");
        if(!$orderBy){
            $orderBy = 'id';
        }
        $pedidos = null;
        if($status){
            $pedidos = Pedido::where('tp_status', $status)->orderBy($orderBy)->get();
        }else{
            $pedidos = Pedido::orderBy($orderBy)->get();
        }
        if($comItens){
            foreach ($pedidos as $pedido) {
                $pedido['itens'] = PedidosProduto::where('id_pedido', $pedido->id)
                    ->join('produtos', 'pedidos_produtos.id_produto', '=', 'produtos.id')->select('pedidos_produtos.*', 'produtos.nome as nm_produto')->get();
            }
        }

        return response()->json($pedidos);

    }

    public function show($id)
    {
        $pedido = Pedido::find($id);
        $pedido->dh_pedido = date('Y-m-d H:i:s', Carbon::parse($pedido->created_at)->timestamp);
        $pedido['itens'] = PedidosProduto::where('id_pedido', $id)
            ->join('produtos', 'pedidos_produtos.id_produto', '=', 'produtos.id')->select('pedidos_produtos.*', 'produtos.nome as nm_produto')->get();
        return response()->json($pedido);
    }

    public function store(Request $request)
    {

        $usrSrv = new UsuariosService($request->user()->id_perfil);
        if (!$usrSrv->verificarPermissao(['pedidos'])) {
            abort(403, 'Você não tem permissão para acessar essa página. [pedidos]');
        }

        $itens = $request->itens;
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
            $valorTotal += $itensMapeados[count($itensMapeados)-1]["vl_total"];
            if(EstoqueService::obterEstoqueDisponivel($produto->id) < $item['quantidade']){
                $produtosInvativos[] = $produto->nome;
            }
        }

        if(!empty($produtosInvativos)){
            EstoqueService::atualizarProdutosAtivos();
            return response()->json([
                "message" => "Enquanto você fazia o pedido, os seguintes itens não estão mais disponíveis: " .  implode(', ', $produtosInvativos)
            ], 422);
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

        EstoqueService::atualizarProdutosAtivos();
        $mensagem = "Olá " . $pedido->nm_cliente . ", recebemos seu pedido #". $pedido->id ." e o status dele é: " . $pedido->tp_status;
        EnviarMensagemWhatsApp::enviar($pedido->nr_telefone, $mensagem);
        return response()->json($pedido, 201);
    }

    public function update(Request $request, $id)
    {
        $pedido = Pedido::find($id);
        $pedido->fill($request->all());
        $pedido->save();

        EstoqueService::atualizarProdutosAtivos();

        $mensagem = "Seu pedido #".  $pedido->id ." foi atualizado para: " . $pedido->tp_status;
        EnviarMensagemWhatsApp::enviar($pedido->nr_telefone, $mensagem);

        return response()->json($pedido);
    }

    public function destroy(Request $request, $id)
    {
        $usrSrv = new UsuariosService($request->user()->id_perfil);
        if (!$usrSrv->verificarPermissao(['pedidos'])) {
            abort(403, 'Você não tem permissão para acessar essa página. [pedidos]');
        }
        
        $pedido = Pedido::find($id);
        $pedido->delete();

        EstoqueService::atualizarProdutosAtivos();
        return response()->json(null, 204);
    }
}