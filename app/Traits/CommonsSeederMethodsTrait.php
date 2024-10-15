<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait CommonsSeederMethodsTrait
{
    protected function atualizaIdIncrementalNumerico() : void
    {
        $maxId = $this->model::max('id');  // Obtém o maior ID atual na tabela
        if ($maxId) {
            // Substitua "tenant_types_id_seq" pelo nome correto da sequência para sua tabela e coluna
            $sequenceName = $this->model::getTableName() . '_id_seq';  // Nome da sequência associada à coluna ID da tabela
            DB::statement('SELECT setval(\'' . $sequenceName . '\', ' . ($maxId + 1) . ', false)');
        }
    }
}
