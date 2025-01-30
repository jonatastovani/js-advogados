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
        $this->model = new App\Models\Auth\PermissionModule();
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
            $table->string('slug');
            $table->string('descricao')->nullable();
            
            $table->smallInteger('tenant_type_id')->nullable()->unique();
            $table->foreign('tenant_type_id')->references('id')->on((new App\Models\Auth\TenantType)->getTableName());
            
            $table->jsonb('data')->nullable();

            $table->string('tipo_modulo');
           
            $table->string('tenant_id')->nullable()->unique();
            $table->foreign('tenant_id')->references('id')->on((new App\Models\Auth\Tenant)->getTableName());

            $this->addCommonFieldsCreatedUpdatedDeleted($table);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->model->getTableName());
    }
};
