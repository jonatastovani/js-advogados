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
        $this->model = new App\Models\Servico\ServicoPagamento();
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

            $table->string('numero_pagamento');

            $table->uuid('servico_id');
            $table->foreign('servico_id')->references('id')->on((new App\Models\Servico\Servico)->getTableName());

            $table->uuid('pagamento_tipo_tenant_id');
            $table->foreign('pagamento_tipo_tenant_id')->references('id')->on((new App\Models\Tenant\PagamentoTipoTenant)->getTableName());

            $table->uuid('forma_pagamento_id');
            $table->foreign('forma_pagamento_id')->references('id')->on((new App\Models\Tenant\FormaPagamentoTenant())->getTableName());

            $table->float('valor_total')->nullable();
            $table->float('entrada_valor')->nullable();
            $table->date('entrada_data')->nullable();
            $table->date('parcela_data_inicio')->nullable();
            $table->integer('parcela_quantidade')->nullable();
            $table->integer('parcela_vencimento_dia')->nullable();
            $table->string('cron_expressao', 20)->nullable();
            $table->date('cron_data_inicio')->nullable();
            $table->date('cron_data_fim')->nullable();
            $table->float('parcela_valor')->nullable();
            $table->string('descricao_condicionado')->nullable();
            $table->string('observacao')->nullable();

            $table->smallInteger('status_id');
            $table->foreign('status_id')->references('id')->on((new App\Models\Referencias\PagamentoStatusTipo)->getTableName());

            $table->jsonb('temporary_data')->nullable(); // Armazenará dados temporários, quando o status for um status em análise.

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
