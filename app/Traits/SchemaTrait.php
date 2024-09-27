<?php

namespace App\Traits;

use App\Models\Auth\Domain;
use App\Models\Auth\Tenant;
use App\Models\Auth\TenantUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

trait SchemaTrait
{
    public function createSchemaIfNotExists(string $schemaName)
    {
        DB::statement("CREATE SCHEMA IF NOT EXISTS $schemaName");
    }

    public function addCommonFieldsCreatedUpdatedDeleted(Blueprint $table, $options = [])
    {
        $createdIdNullable = isset($options['createdIdNullable']) ? $options['createdIdNullable'] : false;
        $createdIdReferenced = isset($options['createdIdReferenced']) ? $options['createdIdReferenced'] : true;
        $updatedIdReferenced = isset($options['updatedIdReferenced']) ? $options['updatedIdReferenced'] : true;
        $deletedIdReferenced = isset($options['deletedIdReferenced']) ? $options['deletedIdReferenced'] : true;
        $allNotReferenced = isset($options['allNotReferenced']) ? $options['allNotReferenced'] : false;

        if ($createdIdNullable) {
            $table->uuid('created_user_id')->nullable();
        } else {
            $table->uuid('created_user_id');
        }
        if ($createdIdReferenced && !$allNotReferenced) {
            $table->foreign('created_user_id')->references('id')->on(TenantUser::getTableName());
            // $table->foreign('created_user_id')->references('id')->on(User::getTableName());
        }
        $table->string('created_ip')->nullable();
        $table->timestamp('created_at')->useCurrent();

        $table->uuid('updated_user_id')->nullable();
        if ($updatedIdReferenced && !$allNotReferenced) {
            $table->foreign('updated_user_id')->references('id')->on(TenantUser::getTableName());
            // $table->foreign('updated_user_id')->references('id')->on(User::getTableName());
        }
        $table->string('updated_ip')->nullable();
        $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

        $table->uuid('deleted_user_id')->nullable();
        if ($deletedIdReferenced && !$allNotReferenced) {
            $table->foreign('deleted_user_id')->references('id')->on(TenantUser::getTableName());
            // $table->foreign('deleted_user_id')->references('id')->on(User::getTableName());
        }
        $table->string('deleted_ip')->nullable();
        $table->softDeletes();
    }

    public function addIDFieldAsUUID(Blueprint $table)
    {
        $table->uuid('id')->primary();
    }

    public function addTenantIDField(Blueprint $table)
    {
        $table->string('tenant_id');
        $table->foreign('tenant_id')->references('id')->on(Tenant::getTableName());
    }

    public function addDomainIDField(Blueprint $table, array $options = [])
    {
        $nullable = isset($options['nullable']) ? $options['nullable'] : false;
        $table->unsignedBigInteger('domain_id')->nullable($nullable);
        $table->foreign('domain_id')->references('id')->on(Domain::getTableName());
    }
}
