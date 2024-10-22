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
        $this->model = new App\Models\Financeiro\Conta();
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

            $table->string('nome');
            $table->string('descricao')->nullable();

            $table->unsignedBigInteger('conta_subtipo_id');
            $table->foreign('conta_subtipo_id')->references('id')->on(App\Models\Referencias\ContaSubtipo::getTableName());

            $table->string('banco')->nullable();
            $table->json('configuracao')->nullable();

            $table->unsignedSmallInteger('conta_status_id');
            $table->foreign('conta_status_id')->references('id')->on(App\Models\Referencias\ContaStatusTipo::getTableName());

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
