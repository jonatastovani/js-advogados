<?php

namespace Database\Seeders\Auth;

use App\Enums\PessoaPerfilTipoEnum;
use App\Enums\PessoaTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Models\Auth\User;
use App\Models\Auth\UserTenantDomain;
use App\Models\Pessoa\Pessoa;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaPerfil;
use Illuminate\Database\Seeder;

class UserEscritorioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();
        $tenantId = 'jsadvogados';

        $usuarios = [
            [
                'name' => 'Jorge Silva',
                'dados_user' => 'jorgesilva',
            ],
            [
                'name' => 'Jéter Tovani',
                'dados_user' => 'jetertovani'
            ],
            [
                'name' => 'Simone Rossi',
                'dados_user' => 'simonerossi'
            ],
        ];

        foreach ($usuarios as $value) {

            $fisica = PessoaFisica::create([
                "nome" => $value['name'],
                "tenant_id" => $tenantId,
                'created_user_id' => $adminTenantUserId
            ]);

            $pessoa = Pessoa::create([
                "pessoa_dados_type" => PessoaTipoEnum::PESSOA_FISICA->value,
                "pessoa_dados_id" => $fisica->id,
                "tenant_id" => $tenantId,
                'created_user_id' => $adminTenantUserId
            ]);

            PessoaPerfil::create([
                "pessoa_id" => $pessoa->id,
                "perfil_tipo_id" => PessoaPerfilTipoEnum::PARCEIRO->value,
                "tenant_id" => $tenantId,
                'created_user_id' => $adminTenantUserId
            ]);

            $perfilUsuario = PessoaPerfil::create([
                "pessoa_id" => $pessoa->id,
                "perfil_tipo_id" => PessoaPerfilTipoEnum::USUARIO->value,
                "tenant_id" => $tenantId,
                'created_user_id' => $adminTenantUserId
            ]);

            $user = User::create([
                'name' => $value['name'],
                'email' => "{$value['dados_user']}@gmail.com",
                'password' => $value['dados_user'],
                'tenant_id' => $tenantId,
                'pessoa_perfil_id' => $perfilUsuario->id,
                'created_user_id' => $adminTenantUserId
            ]);

            $insert = [
                [
                    'user_id' => $user->id,
                    'tenant_id' => 'jsadvogados',
                    'domain_id' => 2,
                ],
                [
                    'user_id' => $user->id,
                    'tenant_id' => 'jsadvogados',
                    'domain_id' => 3,
                ],
                [
                    'user_id' => $user->id,
                    'tenant_id' => 'jsadvogados',
                    'domain_id' => 4,
                ],
            ];

            foreach ($insert as $data) {
                $data['created_user_id'] = $adminTenantUserId;
                UserTenantDomain::create($data);
            }
        }
    }
}
