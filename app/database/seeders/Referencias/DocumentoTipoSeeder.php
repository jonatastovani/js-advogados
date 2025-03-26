<?php

namespace Database\Seeders\Referencias;

use App\Enums\DocumentoTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class DocumentoTipoSeeder extends Seeder
{
    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Referencias\DocumentoTipo();
    }

    public function run(): void
    {
        $dataList = [];
        foreach (DocumentoTipoEnum::cases() as $enumValue) {
            $dataList[] = $enumValue->detalhes();  // Puxa os detalhes de cada enum
        }

        // Chama o método genérico para inserção/atualização
        $this->setAtualizaIdIncrementalBln(true)->upsertData($dataList);
    }
}
