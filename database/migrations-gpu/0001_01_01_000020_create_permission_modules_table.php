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
        $this->model = new App\Models\Auth\PermissionModule();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->model::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug');
            $table->string('descricao')->nullable();
            
            $table->unsignedBigInteger('tenant_type_id')->nullable()->unique();
            $table->foreign('tenant_type_id')->references('id')->on(App\Models\Auth\TenantType::getTableName());
            
            $table->string('tipo_modulo');
           
            $table->string('tenant_id')->nullable()->unique();
            $table->foreign('tenant_id')->references('id')->on(App\Models\Auth\Tenant::getTableName());

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
