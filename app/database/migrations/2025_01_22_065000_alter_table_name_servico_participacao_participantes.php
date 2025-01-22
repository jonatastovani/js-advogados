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
        $this->model = new \App\Models\Comum\ParticipacaoParticipante();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $oldSchema = 'servico'; // Schema antigo
        $oldTableName = 'servico_participacao_participantes'; // Nome antigo da tabela sem schema
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
