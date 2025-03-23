<?php

namespace Database\Seeders\Referencias;

use App\Enums\DocumentoModeloTipoEnum;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class DocumentoModeloTipoSeeder extends Seeder
{

    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Referencias\DocumentoModeloTipo();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $dataList = [];
        foreach (DocumentoModeloTipoEnum::cases() as $enumValue) {
            $dataList[] = $enumValue->detalhes();  // Puxa os detalhes de cada enum
        }

        // Chama o método genérico para inserção/atualização
        $this->setAtualizaIdIncrementalBln(true)->upsertData($dataList);
    }
}
