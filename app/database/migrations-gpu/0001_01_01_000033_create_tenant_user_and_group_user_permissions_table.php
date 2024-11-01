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
        $this->model = new App\Models\Auth\TenantUserAndGroupUserPermisson();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->model::getTableName(), function (Blueprint $table) {
            $this->addIDFieldAsUUID($table);

            $this->addTenantIDField($table);

            $table->uuid('tenant_user_id');
            $table->foreign('tenant_user_id')->references('id')->on(App\Models\Auth\TenantUser::getTableName());

            $table->unsignedBigInteger('permissao_id');
            $table->foreign('permissao_id')->references('id')->on(App\Models\Auth\Permission::getTableName());

            $table->boolean('substituto_bln')->default(false); // True para permissão que é substituto de alguma permissão que compete a diretor

            $table->date('data_inicio');
            $table->date('data_termino')->nullable();

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
