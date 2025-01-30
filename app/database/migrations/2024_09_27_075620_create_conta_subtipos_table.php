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
        $this->model = new App\Models\Referencias\ContaSubtipo();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createSchemaIfNotExists($this->model::getSchemaName());
        Schema::create($this->model->getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();

            $table->unsignedSmallInteger('conta_tipo_id');
            $table->foreign('conta_tipo_id')->references('id')->on((new App\Models\Referencias\ContaTipo)->getTableName());
            
            $table->jsonb('data')->nullable();
            $table->boolean('ativo_bln')->default(true);

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
