<?php

namespace Database\Seeders\Referencias;

use App\Enums\LancamentoStatusTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class LancamentoStatusTipoSeeder extends Seeder
{
    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Referencias\LancamentoStatusTipo();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataList = [];
        foreach (LancamentoStatusTipoEnum::cases() as $enumValue) {
            $dataList[] = $enumValue->detalhes();  // Puxa os detalhes de cada enum
        }

        // Chama o método genérico para inserção/atualização
        $this->setAtualizaIdIncrementalBln(true)->upsertData($dataList);
    }
}
