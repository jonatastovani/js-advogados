<?php

namespace Database\Seeders\Pessoa;

use App\Enums\PessoaPerfilTipoEnum;
use App\Enums\PessoaTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Models\Pessoa\Pessoa;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaPerfil;
use Illuminate\Database\Seeder;

class PessoaPerfilParceiroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();
        for ($i = 0; $i < 15; $i++) {
            $pessoa = new Pessoa();
            $pessoa->tenant_id = 'jsadvogados';
            $pessoa->pessoa_tipo_id = PessoaTipoEnum::PESSOA_FISICA;
            $pessoa->created_user_id = $adminTenantUserId;
            $pessoa->save();

            PessoaFisica::factory()->comPessoaId($pessoa->id)->create();

            $perfil = new PessoaPerfil();
            $perfil->tenant_id = 'jsadvogados';
            $perfil->pessoa_id = $pessoa->id;
            $perfil->perfil_tipo_id = PessoaPerfilTipoEnum::PARCEIRO;
            $perfil->created_user_id = $adminTenantUserId;
            $perfil->save();
        }
    }
}
