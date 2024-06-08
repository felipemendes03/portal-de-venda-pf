<?php

namespace App\Services;

use App\Models\Permissao;
use App\Models\PerfisPermissao;

class UsuariosService{

    private $permissions = [];

    public function __construct(Int $profileId)
    {

        $permissions = PerfisPermissao::where('id_perfil', '=', $profileId)->get();
        $permissoesId = $permissions->pluck('id_permissao')->toArray();
        $permissionModel = Permissao::whereIn('id', $permissoesId)->get();
        $permissionModel->pluck('cd_permissao')->toArray();
        $this->permissions = $permissionModel->pluck('cd_permissao')->toArray();
        
    }

    public function verificarPermissao(array $permissions) : bool {
        return count(array_intersect($permissions, $this->permissions)) > 0;
    }
}