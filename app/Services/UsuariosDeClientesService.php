<?php

namespace App\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class UsuariosDeClientesService{


    public static function validarJwt($jwt){
        try{
            $key = new Key(env('JWT_SECRET'), 'HS256');
            $decoded = JWT::decode($jwt, $key);
            error_log(json_encode($decoded));
            return $decoded->sub;
        }catch(\Exception $e){
            error_log("Erro: " . $e->getMessage());
            return null;
        }
    }

    public static function obterJwt($clienteId){
        $payload = [
            'iss' => "lumen-jwt",
            'sub' => $clienteId,
            'iat' => time(),
            'exp' => time() + 60*60*3
        ];

        return JWT::encode($payload, env('JWT_SECRET'), 'HS256');
    }

}