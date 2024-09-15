<?php

namespace App\Http\Controllers;

use App\Models\CadastroCliente;
use App\Services\UsuariosService;
use Illuminate\Http\Request;

class ClientesController extends Controller {

    public function __construct(Request $request)
    {
        $usrSrv = new UsuariosService($request->user()->id_perfil);
        if (!$usrSrv->verificarPermissao(['cadastro.cliente'])) {
            abort(403, 'Você não tem permissão para acessar essa página. [cadastro.cliente]');
        }
    }

    public function index(Request $request)
    {
        $clientes = CadastroCliente::all();
        return response()->json($clientes);
    }

    public function show(Request $request, $id)
    {
        $cliente = CadastroCliente::find($id);
        return response()->json($cliente);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required',
            'cpf' => 'required|size:14',
            'password' => 'required|min:6',
            'ativo' => 'required',
            'whatsapp' => 'nullable|size:13'
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.size' => 'O CPF deve ter 14 caracteres.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
            'ativo.required' => 'O status ativo é obrigatório.',
            'whatsapp.size' => 'O WhatsApp deve ter 13 caracteres ou ser vazio.',
        ]);

        $cliente = new CadastroCliente();
        $cliente->nome = $request->nome;
        $cliente->cpf = $request->cpf;
        $cliente->ativo = $request->ativo;
        $cliente->password = md5($request->password);
        $cliente->fiado = $request->fiado;
        $cliente->whatsapp = $request->whatsapp;

        $cliente->save();
        return response()->json(['message' => 'Cliente cadastrado com sucesso']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required',
            'cpf' => 'required|size:14',
            'ativo' => 'required',
            'password' => 'nullable|min:6',
            'whatsapp' => 'nullable|size:13'
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.size' => 'O CPF deve ter 14 caracteres.',
            'ativo.required' => 'O status ativo é obrigatório.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
            'whatsapp.size' => 'O WhatsApp deve ter 13 caracteres ou ser vazio.',
        ]);

        $cliente = CadastroCliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente não encontrado'], 404);
        }

        $cliente->nome = $request->nome;
        $cliente->cpf = $request->cpf;
        $cliente->ativo = $request->ativo;
        $cliente->fiado = $request->fiado;
        $cliente->whatsapp = $request->whatsapp;
        
        if ($request->password) {
            $cliente->password = md5($request->password);
        }

        $cliente->save();
        return response()->json(['message' => 'Cliente atualizado com sucesso']);
    }
   

}
