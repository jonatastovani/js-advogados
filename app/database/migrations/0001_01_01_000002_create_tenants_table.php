<?php

declare(strict_types=1);

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
        $this->model = new App\Models\Auth\Tenant();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->createSchemaIfNotExists($this->model::getSchemaName());
        Schema::create($this->model::getTableName(), function (Blueprint $table) {
            $table->string('id')->primary();

            $table->string('nome')->nullable();

            $table->unsignedBigInteger('tenant_type_id');
            $table->foreign('tenant_type_id')->references('id')->on(App\Models\Auth\TenantType::getTableName());
            // fim das colunas personalizadas

            $table->json('data')->nullable();
            $this->addCommonFieldsCreatedUpdatedDeleted($table, ['allNotReferenced' => true, 'createdIdNullable' => true]);
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
