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
        $this->model = new App\Models\Financeiro\MovimentacaoConta();
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

            $table->smallInteger('movimentacao_tipo_id');
            $table->foreign('movimentacao_tipo_id')->references('id')->on((new App\Models\Referencias\MovimentacaoContaTipo)->getTableName());

            $table->uuidMorphs('referencia');

            $table->uuid('conta_domain_id');
            $table->foreign('conta_domain_id')->references('id')->on((new App\Models\Tenant\ContaTenantDomain())->getTableName());

            $table->float('valor_movimentado');
            $table->float('saldo_atualizado');
            $table->date('data_movimentacao');

            $table->string('observacao')->nullable();
            $table->string('descricao_automatica')->nullable();

            $table->smallInteger('status_id');
            $table->foreign('status_id')->references('id')->on((new App\Models\Referencias\MovimentacaoContaStatusTipo)->getTableName());
          
            $table->jsonb('metadata')->nullable();

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
