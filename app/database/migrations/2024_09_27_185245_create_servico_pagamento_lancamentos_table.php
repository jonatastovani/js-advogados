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
        Schema::create($this->model->getTableName(), function (Blueprint $table) {
            $this->addIDFieldAsUUID($table);
            $this->addTenantIDField($table);
            $this->addDomainIDField($table);

            $table->uuid('pagamento_id');
            $table->foreign('pagamento_id')->references('id')->on((new App\Models\Servico\ServicoPagamento)->getTableName());

            $table->string('descricao_automatica');
            $table->string('observacao')->nullable();
            $table->float('valor_esperado');
            $table->date('data_vencimento');
            $table->float('valor_recebido')->nullable();
            $table->date('data_recebimento')->nullable();

            $table->uuid('conta_id')->nullable();
            $table->foreign('conta_id')->references('id')->on((new App\Models\Tenant\ContaTenant)->getTableName());

            $table->smallInteger('status_id');
            $table->foreign('status_id')->references('id')->on((new App\Models\Referencias\LancamentoStatusTipo)->getTableName());

            // Logo abaixo tem a adição da coluna parent_id para refere-se ao pagamento principal, caso seja um pagamento parcial.

            $table->json('metadata')->nullable(); // Armazenará opcionais como rastreamento ou informações originais (ex: em casos de pagamentos parciais, salvar o nome do primeiro pagamento, assim se o restante do parcial gerar mais paciais, levará o nome original.).

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
