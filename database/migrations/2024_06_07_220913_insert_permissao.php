<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->down();

        DB::insert("insert into permissoes (id, nm_permissao, cd_permissao) values (1, 'Dashboard','dashboard');");
        DB::insert("insert into permissoes (id, nm_permissao, cd_permissao) values (2, 'Menu de Produtos','produtos');");
        DB::insert("insert into permissoes (id, nm_permissao, cd_permissao) values (3, 'Menu de Pedidos','pedidos');");
        DB::insert("insert into permissoes (id, nm_permissao, cd_permissao) values (4, 'Menu de Cozinha','cozinha');");
        DB::insert("insert into permissoes (id, nm_permissao, cd_permissao) values (5, 'Menu de Entrega','entrega');");
        DB::insert("insert into permissoes (id, nm_permissao, cd_permissao) values (6, 'Menu de Usuários','usuarios');");
        
        DB::insert("insert into perfis (id, nm_perfil) values (1, 'Administrador');");
        
        DB::insert("insert into perfis_permissoes (id, id_perfil, id_permissao) values (1, 1, 1);");
        DB::insert("insert into perfis_permissoes (id, id_perfil, id_permissao) values (2, 1, 2);");
        DB::insert("insert into perfis_permissoes (id, id_perfil, id_permissao) values (3, 1, 3);");
        DB::insert("insert into perfis_permissoes (id, id_perfil, id_permissao) values (4, 1, 4);");
        DB::insert("insert into perfis_permissoes (id, id_perfil, id_permissao) values (5, 1, 5);");
        DB::insert("insert into perfis_permissoes (id, id_perfil, id_permissao) values (6, 1, 6);");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::delete("delete from perfis_permissoes;");
        DB::delete("delete from perfis;");
        DB::delete("delete from permissoes;");
    }
};
