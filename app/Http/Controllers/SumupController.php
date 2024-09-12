<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\SumupPagamentos;
use Faker\Provider\Uuid;
use Illuminate\Http\JsonResponse;

class SumupController extends Controller {


    public function gerarCheckout(int $pedidoId) : JsonResponse
    {
        $pedido = Pedido::find($pedidoId);

        if (!$pedido) {
            abort('404', message: 'Pedido não encontrado');
        }

        if ($pedido->tp_status != 'PENDENTE_PAGAMENTO') {
            abort('400', 'Pedido não está pendente de pagamento');
        }
        
        $client = new \GuzzleHttp\Client();
        $sumup = $this->getSumupPagamento($pedidoId);
        $response = null;

        if($sumup->tp_situacao != 'NEW'){
            $response = $this->getStatusPagamento($sumup->id_checkout_reference);
        }
        
        if($response && $response['status'] != 'PENDING'){
            if($response['status'] == 'FAILED'){
                $sumup->tp_situacao = 'FAILED';
                $sumup->save();
                $sumup = $this->criarSumupPagamento($pedidoId);
                $response = null;
            }elseif($response['status'] == 'PAID'){
                $sumup->tp_situacao = 'PAID';
                $sumup->save();
                if($pedido->tp_status == 'PENDENTE_PAGAMENTO') {
                    $pedido->tp_status = 'EM_PREPARO';
                    $pedido->save();
                }
                abort('400', 'Pedido já foi pago');
            }else{
                abort('400', 'Pedido não está pendente de pagamento na Sumup, favor dirigir-se ao caixa');
            }
        }

        if(!$response){
            $response = $client->request('POST', 'https://api.sumup.com/v0.1/checkouts', [
                'headers' => [
                    'Authorization' => 'Bearer '. env('API_SUMUP_SECRET_KEY'),
                    'Content-Type' => 'application/json'],
                'json' => [
                    'checkout_reference' => $sumup->id_checkout_reference,
                    'amount' => $pedido->vl_total,
                    'currency' => 'BRL',
                    'pay_to_email' => env('API_SUMUP_EMAIL'),
                    'description' => 'Social. Pedido n'. $pedido->id,
                ]])->getBody()->getContents();
                $response = json_decode($response, true);
        }
       
        $sumup->tp_situacao = 'PENDING';
        $sumup->save();
        return response()->json($response, 200);
    }

    public function confirmarPagamento(int $pedidoId) : JsonResponse
    {
        $pedido = Pedido::find($pedidoId);

        if (!$pedido) {
            abort('404', message: 'Pedido não encontrado');
        }

        if ($pedido->tp_status != 'PENDENTE_PAGAMENTO') {
            abort('400', 'Pedido não está pendente de pagamento');
        }

        $sumup = $this->getSumupPagamento($pedidoId);
        if(!$sumup){
            abort('400', 'Pedido não localizado');
        }
        
        $response = $this->getStatusPagamento($sumup->id_checkout_reference);

        if ($response['status'] == 'PAID') {
            $pedido->tp_status = 'EM_PREPARO';
            $pedido->save();
            $sumup->tp_situacao = 'PAID';
            $sumup->save();
        } else {
            $sumup->tp_situacao = $response['status'];
            $sumup->save();
            abort('400', 'Houve erro na tentativa de pagamento');
        }

        return response()->json($response, 200);
    }

    private function getStatusPagamento($referenceId){
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', "https://api.sumup.com/v0.1/checkouts?checkout_reference=$referenceId",
         ['headers' => [
                'Authorization' => 'Bearer '. env('API_SUMUP_SECRET_KEY'),
                'Content-Type' => 'application/json']]);
        
        $data = json_decode($response->getBody()->getContents(), true);
        return !empty($data) ? $data[0] : null;
    }

    private function getSumupPagamento(int $pedidoId) : SumupPagamentos {
        $sumup = SumupPagamentos::where('id_pedido', $pedidoId)
            ->orderBy('created_at', 'desc')
            ->first();

        if(!$sumup){
            $sumup = $this->criarSumupPagamento($pedidoId);
        }
        return $sumup;
    }

    private function criarSumupPagamento(int $pedidoId) : SumupPagamentos {
        $sumup = new SumupPagamentos();
        $sumup->id_pedido = $pedidoId;
        $sumup->id_checkout_reference = Uuid::uuid();
        $sumup->tp_situacao = 'NEW';
        $sumup->save();
        return $sumup;
    }



}
