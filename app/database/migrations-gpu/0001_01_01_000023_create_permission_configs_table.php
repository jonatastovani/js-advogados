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
        $this->model = new App\Models\Auth\PermissionConfig();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->model::getTableName(), function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('permissao_id')->unique();
            $table->foreign('permissao_id')->references('id')->on(App\Models\Auth\Permission::getTableName());

            $table->boolean('permite_subst_bln')->default(false);
            $table->boolean('gerencia_perm_bln')->default(false);

            $table->unsignedBigInteger('permissao_pai_id')->nullable();
            $table->foreign('permissao_pai_id')->references('id')->on(App\Models\Auth\Permission::getTableName());

            $table->unsignedBigInteger('grupo_id');
            $table->foreign('grupo_id')->references('id')->on(App\Models\Auth\PermissionGroup::getTableName());

            $table->integer('ordem')->nullable();

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
