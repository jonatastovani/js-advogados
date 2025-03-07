<?php

namespace Database\Seeders\Auth;

use App\Enums\TenantTypeEnum;
use App\Helpers\UUIDsHelpers;
use App\Models\Auth\Tenant;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class TenantEscritorioSeeder extends Seeder
{
    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Auth\Tenant();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $idAdmin = UUIDsHelpers::getAdminTenantUser();

        $dataList = [
            [
                'id' => 'jsadvogados',
                'tenant_type_id' => TenantTypeEnum::ADVOCACIA_MANUAL->value,
                'name' => 'JS-Advogados',
                'created_user_id' => $idAdmin,
                'sigla' => 'JS-Adv',

                // Redirecionamento por ser advocacia com identificação de domínio manual
                'redirection_domain_id' => 2
            ],
        ];

        // Chama o método genérico para inserção/atualização
        $this->upsertData($dataList);
    }
}
