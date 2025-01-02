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
        $this->model = new App\Models\Auth\PermissionConfig();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createSchemaIfNotExists($this->model::getSchemaName());
        Schema::create($this->model->getTableName(), function (Blueprint $table) {
            $table->id();

            $table->smallInteger('permissao_id')->unique();
            $table->foreign('permissao_id')->references('id')->on((new App\Models\Auth\Permission)->getTableName());

            $table->boolean('permite_subst_bln')->default(false);
            $table->boolean('gerencia_perm_bln')->default(false);

            $table->smallInteger('permissao_pai_id')->nullable();
            $table->foreign('permissao_pai_id')->references('id')->on((new App\Models\Auth\Permission)->getTableName());

            $table->smallInteger('grupo_id');
            $table->foreign('grupo_id')->references('id')->on((new App\Models\Auth\PermissionGroup)->getTableName());

            $table->integer('ordem')->nullable();

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
