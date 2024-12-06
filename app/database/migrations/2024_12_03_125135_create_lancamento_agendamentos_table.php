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
        $this->model = new App\Models\Financeiro\LancamentoAgendamento();
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

            $table->string('descricao');
            $table->float('valor_esperado')->nullable();
            $table->date('data_vencimento')->nullable();

            $table->uuid('categoria_id');
            $table->foreign('categoria_id')->references('id')->on((new App\Models\Tenant\LancamentoCategoriaTipoTenant())->getTableName());

            $table->uuid('conta_id');
            $table->foreign('conta_id')->references('id')->on((new App\Models\Financeiro\Conta)->getTableName());

            $table->boolean('recorrente_bln')->default(true);
            $table->string('cron_expressao')->nullable();
            $table->date('cron_data_inicio')->nullable();
            $table->date('cron_data_fim')->nullable();
            $table->timestamp('cron_ultima_execucao')->nullable();

            $table->boolean('ativo_bln')->default(true);
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
