<?php

use App\Traits\MigrateTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use MigrateTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new App\Models\Pessoa\PessoaDocumento();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createSchemaIfNotExists($this->model::getSchemaName());

        Schema::table($this->model->getTableName(), function (Blueprint $table) {
            // Renomeando o campo de 'campos_adicionais' para 'data'
            $table->renameColumn('campos_adicionais', 'data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table($this->model->getTableName(), function (Blueprint $table) {
            // Revertendo de 'data' para 'campos_adicionais'
            $table->renameColumn('data', 'campos_adicionais');
        });
    }
};
