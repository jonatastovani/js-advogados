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
        $this->model = new App\Models\Servico\ServicoPagamentoLancamento();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createSchemaIfNotExists($this->model::getSchemaName());
        Schema::create($this->model::getTableName(), function (Blueprint $table) {
            $this->addIDFieldAsUUID($table);
            $this->addTenantIDField($table);
            $this->addDomainIDField($table);

            $table->uuid('pagamento_id');
            $table->foreign('pagamento_id')->references('id')->on(App\Models\Servico\ServicoPagamento::getTableName());

            $table->string('descricao_automatica');
            $table->string('observacao')->nullable();
            $table->float('valor_esperado');
            $table->string('data_vencimento');
            $table->float('valor_recebido')->nullable();
            $table->string('data_recebimento')->nullable();

            $table->uuid('conta_id');
            $table->foreign('conta_id')->references('id')->on(App\Models\Financeiro\Conta::getTableName());
            
            $table->unsignedBigInteger('status_id');
            $table->foreign('status_id')->references('id')->on(\App\Models\Referencias\ServicoPagamentoLancamentoStatusTipo::getTableName());

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
