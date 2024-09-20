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
        Schema::table('cadastro_cliente', function (Blueprint $table) {
            $table->boolean('fiado')->default(false)->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cadastro_cliente', function (Blueprint $table) {
            $table->dropColumn('fiado');
        });
    }

};
