<?php

namespace Database\Seeders\Referencias;

use App\Enums\PessoaPerfilTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class PessoaPerfilTipoSeeder extends Seeder
{
    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Referencias\PessoaPerfilTipo();
    }

    public function run(): void
    {
        $dataList = [];
        foreach (PessoaPerfilTipoEnum::cases() as $enumValue) {
            $dataList[] = $enumValue->detalhes();  // Puxa os detalhes de cada enum
        }

        // Chama o método genérico para inserção/atualização
        $this->setAtualizaIdIncrementalBln(true)->upsertData($dataList);
    }
}
