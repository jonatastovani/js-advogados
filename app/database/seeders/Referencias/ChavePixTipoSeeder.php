<?php

namespace Database\Seeders\Referencias;

use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\ChavePixTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class ChavePixTipoSeeder extends Seeder
{
    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Referencias\ChavePixTipo();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataList = [];
        foreach (ChavePixTipoEnum::cases() as $enumValue) {
            $dataList[] = $enumValue->detalhes();  // Puxa os detalhes de cada enum
        }

        // Chama o método genérico para inserção/atualização
        $this->setAtualizaIdIncrementalBln(true)->upsertData($dataList);
    }
}
