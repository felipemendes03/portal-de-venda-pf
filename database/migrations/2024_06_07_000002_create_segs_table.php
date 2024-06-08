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
        Schema::create('permissoes', function (Blueprint $table) {
            $table->id();
            $table->string('nm_permissao', 100);
            $table->string('cd_permissao', 100);
        });

        Schema::create('perfis', function (Blueprint $table) {
            $table->id();
            $table->string('nm_perfil', 100);
        });

        Schema::create('perfis_permissoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_perfil');
            $table->unsignedBigInteger('id_permissao');
            $table->foreign('id_perfil')->references('id')->on('perfis');
            $table->foreign('id_permissao')->references('id')->on('permissoes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::dropIfExists('perfis_permissoes');
        Schema::dropIfExists('permissoes');
        Schema::dropIfExists('perfis');
    }
};
