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
        $this->model = new \App\Models\Financeiro\MovimentacaoContaParticipante();
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

            $table->uuidMorphs('parent'); // Qual model lançou o participante
            $table->nullableUuidMorphs('referencia'); // Perfil Participante referenciado

            $table->string('descricao_automatica'); // Descreve automaticamente para não ser alterado futuramente as informações
            $table->decimal('valor_participante', 10, 2);

            $table->uuid('participacao_tipo_id');
            $table->foreign('participacao_tipo_id', "{fk_{$this->model->getTableAsName()}_participacao_tipo_id")->references('id')->on((new App\Models\Tenant\ParticipacaoTipoTenant)->getTableName());

            $table->smallInteger('participacao_registro_tipo_id');
            $table->foreign('participacao_registro_tipo_id', "fk_{$this->model->getTableAsName()}_participacao_registro_tipo_id")->references('id')->on((new App\Models\Referencias\ParticipacaoRegistroTipo)->getTableName());
          
            $table->smallInteger('status_id');
            $table->foreign('status_id')->references('id')->on((new App\Models\Referencias\MovimentacaoContaParticipanteStatusTipo())->getTableName());
                    
            $table->json('metadata')->nullable();

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
