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
        $this->model = new \App\Models\Servico\ServicoParticipacaoParticipante();
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

            $table->uuidMorphs('parent');

            $table->uuid('participacao_tipo_id');
            $table->foreign('participacao_tipo_id', "{fk_{$this->model->getTableAsName()}_participacao_tipo_id")->references('id')->on((new App\Models\Tenant\ServicoParticipacaoTipoTenant)->getTableName());

            $table->smallInteger('participacao_registro_tipo_id');
            $table->foreign('participacao_registro_tipo_id', "fk_{$this->model->getTableAsName()}_participacao_registro_tipo_id")->references('id')->on((new App\Models\Referencias\ParticipacaoRegistroTipo)->getTableName());

            $table->nullableUuidMorphs('referencia');

            $table->string('nome_grupo')->nullable();
            $table->enum('valor_tipo', ['porcentagem', 'valor_fixo']);
            $table->decimal('valor', 10, 2);
            $table->string('observacao')->nullable();

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
