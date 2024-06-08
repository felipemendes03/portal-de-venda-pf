<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\Perfil;
use App\Models\User;
use App\Services\UsuariosService;
use App\UseCase\Permission\CheckPermissionUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SecurityUserContorller extends Controller
{
 
    public function __construct(Request $request)
    {
        $usrSrv = new UsuariosService($request->user()->id_perfil);
        if (!$usrSrv->verificarPermissao(['usuarios'])) {
            abort(403, 'Você não tem permissão para acessar essa página');
        }
    }

    public function getUsers() :  Response{
        return Inertia::render('Seguranca/Usuarios', [
           "users" => User::all(),
           "profiles" => Perfil::all(),
        ]);
    }

    public function saveUser(Request $request) :  Void{
        $id = $request->id;
        $nome = $request->name;
        $email = $request->email;
        $password = $request->password;
        $flagAtivo = $request->fg_ativo;
        $profile = $request->id_perfil;
        $userModel = null;
        if(!$id){
            $userModel = new User();
        } else {
            $userModel = User::find($id);
        }
        if(!$userModel){
            abort(404, "Usuário não encontrado");
        }
        $userModel->name = $nome;
        $userModel->email = $email;
        if($password) {
            $userModel->password = bcrypt($password);
        }
        $userModel->id_perfil = $profile;
        $userModel->fg_ativo = $flagAtivo;
        $userModel->save();
    }

    public function deleteUser($id) :  Void{
        $userModel = User::find($id);
        if(!$userModel){
            abort(404, "Usuário não encontrado");
        }
        $userModel->delete();
    }
}