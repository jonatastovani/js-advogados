<?php

use App\Traits\MigrateTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use MigrateTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Tenant\ParticipacaoTipoTenant();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $oldSchema = 'tenant'; // Schema antigo
        $oldTableName = 'servico_participacao_tipo_tenants'; // Nome antigo da tabela sem schema
        $newSchema = $this->model->getSchemaName(); // Novo schema
        $newTableName = $this->model->getTableWithoutSchema(); // Novo nome da tabela sem schema

        $this->alterTableSchemaAndName($oldSchema, $oldTableName, $newSchema, $newTableName);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não terá alteração de volta
    }
};
