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
        $this->model = new App\Models\Auth\PermissionGroup();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createSchemaIfNotExists($this->model::getSchemaName());
        Schema::create($this->model::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('descricao')->nullable();

            $table->unsignedBigInteger('modulo_id');
            $table->foreign('modulo_id')->references('id')->on(App\Models\Auth\PermissionModule::getTableName());
            
            $table->unsignedBigInteger('grupo_pai_id')->nullable();
            $table->foreign('grupo_pai_id')->references('id')->on(App\Models\Auth\PermissionGroup::getTableName());

            // Se as permissões que estão nesse grupo são atribuídas individualmente ao usuário, ou escalonadas pela ordem dentro do grupo
            $table->boolean('individuais')->default(false);
            $table->boolean('ativo')->default(true);

            $this->addCommonFieldsCreatedUpdatedDeleted($table);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->model::getTableName());
    }
};
