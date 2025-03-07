<?php

namespace Database\Seeders\Auth;

use App\Enums\TenantTypeEnum;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantTypeSeeder extends Seeder
{
    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Auth\TenantType();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataList = [];
        foreach (TenantTypeEnum::cases() as $enumValue) {
            $dataList[] = $enumValue->detalhes();  // Puxa os detalhes de cada enum
        }

        // Chama o método genérico para inserção/atualização
        $this->setAtualizaIdIncrementalBln(true)->upsertData($dataList);
    }
}
