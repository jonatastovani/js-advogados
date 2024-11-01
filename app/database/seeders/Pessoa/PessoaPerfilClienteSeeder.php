<?php

namespace Database\Seeders\Pessoa;

use App\Enums\PessoaPerfilTipoEnum;
use App\Enums\PessoaTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Models\Pessoa\Pessoa;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaPerfil;
use Illuminate\Database\Seeder;

class PessoaPerfilClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();
        $tenantId = 'jsadvogados';

        for ($i = 0; $i < 100; $i++) {
            $fisica = PessoaFisica::factory()->comTenantId($tenantId)->create();

           $pessoa = $fisica->pessoa()->create([
                'tenant_id' => $tenantId,
                'created_user_id' => $adminTenantUserId
            ]);

            $perfil = new PessoaPerfil();
            $perfil->tenant_id = $tenantId;
            $perfil->pessoa_id = $pessoa->id;
            $perfil->perfil_tipo_id = PessoaPerfilTipoEnum::CLIENTE;
            $perfil->created_user_id = $adminTenantUserId;
            $perfil->save();
        }
    }
}
