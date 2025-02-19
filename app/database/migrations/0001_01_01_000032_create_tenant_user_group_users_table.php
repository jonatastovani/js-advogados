<?php

declare(strict_types=1);

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
        $this->model = new App\Models\Auth\TenantUserGroupUser();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->createSchemaIfNotExists($this->model::getSchemaName());
        Schema::create($this->model->getTableName(), function (Blueprint $table) {
            $this->addIDFieldAsUUID($table);

            $this->addTenantIDField($table);

            $table->uuid('tenant_user_group_id');
            $table->foreign('tenant_user_group_id')->references('id')->on((new App\Models\Auth\TenantUserGroup)->getTableName());

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on((new App\Models\Auth\User)->getTableName());
            // $table->uuid('tenant_user_id');
            // $table->foreign('tenant_user_id')->references('id')->on((new TenantUser())->getTableName());
            
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
