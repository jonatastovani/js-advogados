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
        $this->model = new \App\Models\Documento\DocumentoGerado();
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

            $table->string('numero_documento');

            $table->smallInteger('documento_gerado_tipo_id');
            $table->foreign('documento_gerado_tipo_id', "fk_{$this->model->getTableAsName()}_documento_gerado_tipo_id")->references('id')->on((new App\Models\Referencias\DocumentoGeradoTipo)->getTableName());

            $table->jsonb('dados');
            $table->jsonb('data')->nullable();

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
