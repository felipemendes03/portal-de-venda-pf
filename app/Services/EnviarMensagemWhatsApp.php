<?php

namespace App\Services;

class EnviarMensagemWhatsApp{

    public static function enviar($telefone, $mensagem){
        error_log("Enviando mensagem para $telefone: $mensagem");
        $habilitado = env('API_WHATSAPP_ENABLED');
        if(!$habilitado || !$telefone || !$mensagem){
            return;
        }

        $client = new \GuzzleHttp\Client();
        $token = env('API_WHATSAPP_TOKEN');
        $chaveDaInstancia = env('API_WHATSAPP_INSTANCE_KEY');
        $url = env('API_WHATSAPP_URL') . "/message/text?key=$chaveDaInstancia";
        
        $respose = $client->request('POST', $url, [
            'headers' => ["Authorization" => "Bearer $token"],
            'form_params' => [
                'id' => $telefone,
                'message' => $mensagem
            ]])->getBody();
        
        error_log($respose);
    }

}
