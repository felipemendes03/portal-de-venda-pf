<?php

namespace App\Http\Controllers;

use App\Models\CadastroCliente;
use App\Services\UsuariosDeClientesService;
use Illuminate\Http\Request;

class CadastroClienteController extends Controller {

    public function login(Request $request){

        $request->validate([
            'cpf' => 'required|size:14',
            'senha' => 'required|min:6'
        ]);

        $cliente = CadastroCliente::where('cpf', $request->cpf)
            ->where('password', md5($request->senha))
            ->first();

        if($cliente){
            $jwt = UsuariosDeClientesService::obterJwt($cliente->id);
            return response()->json(['token' => $jwt, 'token_type' => 'Bearer'], 200);
        }

        return response()->json(['error' => 'CPF ou senha inválidos'], 401);
    }

    public function verificarSeExiste(Request $request){
        $request->validate([
            'cpf' => 'required|size:14'
        ]);
        return response()->json(['existe' => CadastroCliente::where('cpf', $request['cpf'])->exists()], 200);
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
            'cpf' => 'required|size:14',
            'senha' => 'required|min:6'
        ]);

        if(CadastroCliente::where('cpf', $request->cpf)->exists()){
            return response()->json(['error' => 'CPF já cadastrado'], 400);
        }

        $cliente = new CadastroCliente();
        $cliente->nome = $request->nome;
        $cliente->cpf = $request->cpf;
        $cliente->password = md5($request->senha);
        $cliente->save();

        $jwt = UsuariosDeClientesService::obterJwt($cliente->id);

        return response()->json(['token' => $jwt, 'token_type' => 'Bearer'], 201);
    }

}
