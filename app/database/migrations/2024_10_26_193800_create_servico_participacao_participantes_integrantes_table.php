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
        $this->model = new App\Models\Servico\ServicoParticipacaoParticipanteIntegrante();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createSchemaIfNotExists($this->model::getSchemaName());
        Schema::create($this->model->getTableName(), function (Blueprint $table) {
            $this->addIDFieldAsUUID($table);
            $this->addTenantIDField($table);
            $this->addDomainIDField($table);

            $table->uuid('participante_id');
            $table->foreign('participante_id', "fk_{$this->model->getTableAsName()}_participante_id")->references('id')->on((new App\Models\Servico\ServicoParticipacaoParticipante)->getTableName());

            $table->smallInteger('participacao_registro_tipo_id');
            $table->foreign('participacao_registro_tipo_id', "{fk_{$this->model->getTableAsName()}_participacao_registro_tipo_id")->references('id')->on((new App\Models\Referencias\ParticipacaoRegistroTipo)->getTableName());

            $table->uuidMorphs('referencia');

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
        Schema::dropIfExists($this->model->getTableName());
    }
};
