<?php

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
        $this->model = new App\Models\GPU\Inteligencia\InformacaoSubjetivaPessoaEnvolvida();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->model::getTableName(), function (Blueprint $table) {
            $this->addIDFieldAsUUID($table);
            
            $table->uuid('informacao_id');
            $table->foreign('informacao_id')->references('id')->on(app\Models\GPU\Inteligencia\InformacaoSubjetiva::getTableName());

            $table->unsignedBigInteger('referencia_id');
            
            $table->unsignedBigInteger('pessoa_tipo_tabela_id');
            $table->foreign('pessoa_tipo_tabela_id')->references('id')->on(app\Models\GPU\PessoaTipoTabela::getTableName());

            $this->addCommonFieldsCreatedUpdatedDeleted($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->model::getTableName());
    }
};
