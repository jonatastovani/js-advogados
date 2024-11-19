<?php

declare(strict_types=1);

use App\Traits\SchemaTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use SchemaTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new App\Models\Servico\ServicoPagamentoLancamento();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->model->getTableName(), function (Blueprint $table) {

            $table->uuid('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on($this->model->getTableName());

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table($this->model->getTableName(), function (Blueprint $table) {
            // // Remove a chave estrangeira e a coluna 'pessoa_perfil_id'
            // $table->dropForeign(['parent_id']);
            // $table->dropColumn('parent_id');
        });
    }
};
