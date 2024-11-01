<?php

namespace Database\Seeders;

use App\Models\Auth\TenantType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insert = [
            [
                'id' => 1,
                'nome' => 'Administrador',
                'descricao' => 'Tenant Central de Administração do Sistema.',
            ],
            [
                'id' => 2,
                'nome' => 'Api',
                'descricao' => 'Tenant somente para APIs.',
            ],
            [
                'id' => 3,
                'nome' => 'Advocacia',
                'descricao' => 'Módulo Advocacia.',
            ],
        ];

        foreach ($insert as $data) {
            TenantType::create($data);
        }

        $maxId = TenantType::max('id');  // Obtém o maior ID atual na tabela

        if ($maxId) {
            // Substitua "tenant_types_id_seq" pelo nome correto da sequência para sua tabela e coluna
            $sequenceName = TenantType::getTableName() . '_id_seq';  // Nome da sequência associada à coluna ID da tabela
            DB::statement('SELECT setval(\'' . $sequenceName . '\', ' . ($maxId + 1) . ', false)');
        }
    }
}
