<?php

namespace Database\Seeders\Referencias;

use App\Enums\PagamentoTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Models\Referencias\PagamentoTipo;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PagamentoTipoSeeder extends Seeder
{
    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Referencias\PagamentoTipo();
    }

    public function run(): void
    {
        $dataList = [];
        foreach (PagamentoTipoEnum::cases() as $enumValue) {
            $dataList[] = $enumValue->detalhes();  // Puxa os detalhes de cada enum
        }

        // Chama o método genérico para inserção/atualização
        $this->setAtualizaIdIncrementalBln(true)->upsertData($dataList);
    }
}
