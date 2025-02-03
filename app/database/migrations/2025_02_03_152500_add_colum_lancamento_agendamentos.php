<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('financeiro.lancamento_agendamentos', function (Blueprint $table) {
            $table->smallInteger('status_id');
            $table->foreign('status_id')->references('id')->on((new App\Models\Referencias\LancamentoStatusTipo())->getTableName());
        });
    }
    
    public function down()
    {
        Schema::table('financeiro.lancamento_agendamentos', function (Blueprint $table) {
            // $table->dropColumn('status_id');
        });
    }
    
};
