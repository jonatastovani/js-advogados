<?php

namespace Database\Seeders\Referencias;

use App\Models\GPU\FuncionarioGPU;
use App\Models\GPU\PessoaTipoTabela;
use App\Models\GPU\PessoaGPU;
use App\Models\GPU\PresoSincronizacaoGPU;
use Illuminate\Database\Seeder;

class PessoaTipoTabelaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insert = [
            [
                'id' => 1,
                'nome' => 'Preso',
                'descricao' => 'Relacionamento do tipo Preso - Tabela Preso Sincronização',
                'tabela_ref' => PresoSincronizacaoGPU::getTableName(),
                'tabela_model' => PresoSincronizacaoGPU::class,
            ],
            [
                'id' => 2,
                'nome' => 'Pessoa',
                'descricao' => 'Relacionamento do tipo Pessoa GPU',
                'tabela_ref' => PessoaGPU::getTableName(),
                'tabela_model' => PessoaGPU::class,
            ],
            [
                'id' => 3,
                'nome' => 'Funcionário',
                'descricao' => 'Relacionamento do tipo Funcionário',
                'tabela_ref' => FuncionarioGPU::getTableName(),
                'tabela_model' => FuncionarioGPU::class,
            ],
        ];

        foreach ($insert as $data) {
            PessoaTipoTabela::create($data);
        }
    }
}
