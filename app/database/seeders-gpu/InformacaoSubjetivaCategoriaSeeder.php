<?php

namespace Database\Seeders;

use App\Helpers\UUIDsHelpers;
use App\Models\GPU\Inteligencia\InformacaoSubjetivaCategoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InformacaoSubjetivaCategoriaSeeder extends Seeder
{

    private $adminTenantUserId;

    public function __construct(public InformacaoSubjetivaCategoria $model)
    {
        $this->adminTenantUserId = UUIDsHelpers::getAdminTenantUser();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $insert = [
            [
                'nome' => 'Tribunal do crime',
            ],
            [
                'nome' => 'Mídias relevantes',
            ],
            [
                'nome' => 'Inadaptação',
            ],
            [
                'nome' => 'Relevante',
            ],
        ];

        foreach ($insert as $data) {
            $data['created_user_id'] = $this->adminTenantUserId;
            $this->model::create($data);
        }
    }
}
