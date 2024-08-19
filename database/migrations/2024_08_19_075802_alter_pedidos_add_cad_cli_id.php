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
        Schema::table('pedidos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_cadastro_cliente')->nullable()->after('id');
            $table->foreign('id_cadastro_cliente')->references('id')->on('cadastro_cliente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['id_cadastro_cliente']);
            $table->dropColumn('id_cadastro_cliente');
        });
    }

};
