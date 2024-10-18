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
        $this->model = new \App\Models\Servico\ServicoParticipacaoPresetRelacao();
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

            $table->uuid('preset_id');
            $table->foreign('preset_id')->references('id')->on(App\Models\Servico\ServicoParticipacaoPreset::getTableName());

            $table->unsignedBigInteger('participacao_registro_tipo_id');
            $table->foreign('participacao_registro_tipo_id')->references('id')->on(App\Models\Referencias\ParticipacaoRegistroTipo::getTableName());

            $table->uuid('perfil_id');
            // $table->foreign('perfil_id')->references('id')->on(App\Models\Seguranca\Perfil::getTableName());

            $table->uuid('participacao_tipo_id');
            $table->foreign('participacao_tipo_id')->references('id')->on(App\Models\Servico\ServicoParticipacaoTipo::getTableName());

            $table->string('nome_grupo')->nullable();
            $table->decimal('porcentagem', 5)->nullable();
            $table->float('valor_fixo')->nullable();
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
        Schema::dropIfExists($this->model::getTableName());
    }
};
