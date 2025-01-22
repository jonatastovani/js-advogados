<?php

namespace App\Traits;

use App\Models\Auth\Domain;
use App\Models\Auth\Tenant;
use App\Models\Auth\TenantUser;
use App\Models\Auth\UserTenantDomain;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

trait MigrateTrait
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
            $table->foreign('created_user_id')->references('id')->on((new UserTenantDomain())->getTableName());
        }
        $table->string('created_ip')->nullable();
        $table->timestamp('created_at', 6)->useCurrent();

        $table->uuid('updated_user_id')->nullable();
        if ($updatedIdReferenced && !$allNotReferenced) {
            $table->foreign('updated_user_id')->references('id')->on((new UserTenantDomain())->getTableName());
        }
        $table->string('updated_ip')->nullable();
        $table->timestamp('updated_at', 6)->nullable()->useCurrentOnUpdate();

        $table->uuid('deleted_user_id')->nullable();
        if ($deletedIdReferenced && !$allNotReferenced) {
            $table->foreign('deleted_user_id')->references('id')->on((new UserTenantDomain())->getTableName());
        }
        $table->string('deleted_ip')->nullable();
        $table->timestamp('deleted_at', 6)->nullable();
    }

    public function addIDFieldAsUUID(Blueprint $table)
    {
        $table->uuid('id')->primary();
    }

    public function addTenantIDField(Blueprint $table)
    {
        $table->string('tenant_id');
        $table->foreign('tenant_id')->references('id')->on((new Tenant())->getTableName());
    }

    public function addDomainIDField(Blueprint $table, array $options = [])
    {
        $nullable = isset($options['nullable']) ? $options['nullable'] : false;
        $table->unsignedBigInteger('domain_id')->nullable($nullable);
        $table->foreign('domain_id')->references('id')->on((new Domain())->getTableName());
    }

    /**
     * Altera o nome e/ou o schema de uma tabela de forma dinâmica.
     *
     * @param string $oldSchema Nome do schema antigo.
     * @param string $oldTableName Nome da tabela antiga sem schema.
     * @param string $newSchema Nome do novo schema.
     * @param string $newTableName Nome da nova tabela sem schema.
     * @return void
     */
    public function alterTableSchemaAndName(string $oldSchema, string $oldTableName, string $newSchema, string $newTableName): void
    {
        $oldFullTableName = "{$oldSchema}.{$oldTableName}";
        $newFullTableName = "{$newSchema}.{$newTableName}";

        // Verifica se a tabela antiga existe
        if (\Illuminate\Support\Facades\Schema::hasTable($oldFullTableName)) {
            // Cria o novo schema, se não existir
            $this->createSchemaIfNotExists($newSchema);

            // Move a tabela para o novo schema
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$oldFullTableName} SET SCHEMA {$newSchema}");

            // Renomeia a tabela dentro do novo schema
            if ($oldTableName !== $newTableName) {
                \Illuminate\Support\Facades\Schema::rename("{$newSchema}.{$oldTableName}", $newTableName);
            }
        }
    }
}
