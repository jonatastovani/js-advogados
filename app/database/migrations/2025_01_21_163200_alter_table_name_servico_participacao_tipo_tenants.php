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
        $oldTableName = 'tenant.servico_participacao_tipo_tenants'; // Nome antigo da tabela com schema
        $newTableName = $this->model->getTableWithoutSchema();       // Novo nome da tabela

        if (Schema::hasTable($oldTableName)) {
            Schema::rename($oldTableName, $newTableName);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Não terá alteração de volta
        
        // $oldTableName = 'servico_participacao_tipo_tenants'; // Nome antigo da tabela
        // $newTableName = $this->model->getTableName();       // Novo nome da tabela

        // if (Schema::hasTable($newTableName)) {
        //     Schema::rename($newTableName, $oldTableName);
        // }
    }
};
