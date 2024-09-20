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
        DB::insert("insert into permissoes (id, nm_permissao, cd_permissao) values (7, 'Cadastro de cliente','cadastro.cliente');");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::delete("delete from permissoes where id = 7;");
    }
};
