<?php

namespace App\Http\Controllers;

use App\Models\CadastroCliente;
use App\Services\UsuariosDeClientesService;
use Illuminate\Http\Request;

class CadastroClienteController extends Controller {

    public function login(Request $request){

        $request->validate([
            'usuario' => 'required|size:11',
            'senha' => 'required|min:6'
        ]);

        $cliente = CadastroCliente::where('usuario', $request->usuario)
            ->where('password', md5($request->senha))
            ->first();

        if($cliente && $cliente->ativo == 0){
            return response()->json(['error' => 'Usuário inativo'], 401);
        }
        
        if($cliente){
            $jwt = UsuariosDeClientesService::obterJwt($cliente->id);
            return response()->json(['token' => $jwt, 'token_type' => 'Bearer'], 200);
        }

        return response()->json(['error' => 'Usuário ou senha inválidos'], 401);
    }

    public function verificarSeExiste(Request $request){
        $request->validate([
            'usuario' => 'required|size:11'
        ]);
        return response()->json(['existe' => CadastroCliente::where('usuario', $request['usuario'])->exists()], 200);
    }

    public function show(Request $request){
        $headerToken = $request->header('Authorization');
        $jwt = str_replace('Bearer ', '', $headerToken);
        $clienteId = UsuariosDeClientesService::validarJwt($jwt);
        if(!$clienteId){
            return response()->json(['error' => 'Não autorizado'], 401);
        }
        $cliente = CadastroCliente::find($clienteId);
        return response()->json($cliente, 200);
    }

    public function store(Request $request){
        $request->validate([
            'nome' => 'required|min:6',
            'usuario' => 'required|size:11',
            'whatsapp' => 'nullable|size:11',
            'senha' => 'required|min:6',
        ]);

        if(CadastroCliente::where('usuario', $request->usuario)->exists()){
            return response()->json(['error' => 'Usuário já cadastrado'], 400);
        }

        $cliente = new CadastroCliente();
        $cliente->nome = $request->nome;
        $cliente->usuario = $request->usuario;
        $cliente->whatsapp = $request->whatsapp;
        $cliente->password = md5($request->senha);
        $cliente->save();

        $jwt = UsuariosDeClientesService::obterJwt($cliente->id);

        return response()->json(['token' => $jwt, 'token_type' => 'Bearer'], 201);
    }

}
