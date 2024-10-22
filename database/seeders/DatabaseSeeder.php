<?php

namespace Database\Seeders;

use Database\Seeders\Financeiro\ContaSeeder;
use Database\Seeders\Pessoa\PessoaPerfilSeeder;
use Database\Seeders\Tenant\PagamentoTipoTenantSeeder;
use Database\Seeders\Tenant\AreaJuridicaTenantSeeder;
use Database\Seeders\Referencias\ContaStatusTipoSeeder;
use Database\Seeders\Referencias\ContaSubtipoSeeder;
use Database\Seeders\Referencias\ContaTipoSeeder;
use Database\Seeders\Referencias\DocumentoTipoSeeder;
use Database\Seeders\Referencias\PagamentoTipoSeeder;
use Database\Seeders\Referencias\ParticipacaoRegistroTipoSeeder;
use Database\Seeders\Referencias\PessoaPerfilTipoSeeder;
use Database\Seeders\Referencias\PessoaTipoSeeder;
use Database\Seeders\Referencias\ServicoPagamentoLancamentoStatusTipoSeeder;
use Database\Seeders\Referencias\ServicoParticipacaoReferenciaTipoSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TenantTypeSeeder::class,
            TenantDomainSeeder::class,
            UserSeeder::class,
            UserTenantDomainSeeder::class,

            PessoaPerfilTipoSeeder::class,
            PessoaTipoSeeder::class,
            DocumentoTipoSeeder::class,

            PermissionModuleSeeder::class,
            PermissionGroupSeeder::class,
            PermissionSeeder::class,
            PermissionConfigSeeder::class,

            ContaStatusTipoSeeder::class,
            ServicoPagamentoLancamentoStatusTipoSeeder::class,
        ]);


        if (env('APP_ENV') == 'local') {
            $this->call([PessoaPerfilSeeder::class]);
        }

        $this->call([
            ContaTipoSeeder::class,
            ContaSubtipoSeeder::class,
            AreaJuridicaTenantSeeder::class,
            PagamentoTipoSeeder::class,
            PagamentoTipoTenantSeeder::class,
            ContaSeeder::class,
        ]);

        $this->call([
            // ParticipacaoRegistroTipoSeeder::class,
            // ServicoParticipacaoReferenciaTipoSeeder::class,
        ]);
    }
}
