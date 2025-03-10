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
        $this->model = new App\Models\Tenant\ContaTenant();
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

            $table->string('nome');
            $table->string('descricao')->nullable();

            $table->smallInteger('conta_subtipo_id');
            $table->foreign('conta_subtipo_id')->references('id')->on((new App\Models\Referencias\ContaSubtipo)->getTableName());

            $table->string('banco')->nullable();
            $table->jsonb('data')->nullable();

            $table->unsignedSmallInteger('conta_status_id');
            $table->foreign('conta_status_id')->references('id')->on((new App\Models\Referencias\ContaStatusTipo)->getTableName());

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
