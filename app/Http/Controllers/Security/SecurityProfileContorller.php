<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\Perfil;
use App\Models\PerfisPermissao;
use App\Models\Permissao;
use App\Services\UsuariosService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;

class SecurityProfileContorller extends Controller
{
    public function __construct(Request $request)
    {
        $usrSrv = new UsuariosService($request->user()->id_perfil);
        if (!$usrSrv->verificarPermissao(['usuarios'])) {
            abort(403, 'Você não tem permissão para acessar essa página');
        }
    }

    public function getProfiles() :  Response{
        return Inertia::render('Seguranca/Perfis', [
           "profiles" => Perfil::all(),
           "permissions" => Permissao::all(),
        ]);
    }

    public function getProfilePermissions($idProfile) : JsonResponse {
        $ids = [];
        $permissions = PerfisPermissao::where('id_perfil', '=', $idProfile)->get('id_permissao');
        foreach ($permissions as $p) {
            $ids[] = $p->id_permissao;
        }
        return response()->json($ids);
    }

    public function saveProfile(Request $request) :  Void{
        $id = $request->id;
        $nome = $request->nm_perfil;
        $profileModel = null;
        if(!$id){
            $profileModel = new Perfil();
        } else {
            $profileModel = Perfil::find($id);
        }
        if(!$profileModel){
            abort(404, "Perfil não encontrado");
        }
        $profileModel->nm_perfil = $nome;
        $profileModel->save();

        
        PerfisPermissao::where('id_perfil', '=', $profileModel->id)->delete();
        foreach($request->permissionsSelected as $idPermission){
            $profilePermissionsModel = new PerfisPermissao();
            $profilePermissionsModel->id_perfil = $profileModel->id;
            $profilePermissionsModel->id_permissao = $idPermission;
            $profilePermissionsModel->save();
        }
    }

    public function deleteProfile($id) :  Void{
        $profileModel = Perfil::find($id);
        if(!$profileModel){
            abort(404, "Perfil não encontrado");
        }
        $profileModel->delete();
    }
}