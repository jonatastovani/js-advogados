<?php

namespace Database\Seeders;

use Database\Seeders\Financeiro\ContaSeeder;
use Database\Seeders\Financeiro\PagamentoTipoTenantSeeder;
use Database\Seeders\Referencias\AreaJuridicaSeeder;
use Database\Seeders\Referencias\ContaStatusTipoSeeder;
use Database\Seeders\Referencias\ContaSubtipoSeeder;
use Database\Seeders\Referencias\ContaTipoSeeder;
use Database\Seeders\Referencias\PagamentoTipoSeeder;
use Database\Seeders\Referencias\ParticipacaoRegistroTipoSeeder;
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
            PermissionModuleSeeder::class,
            PermissionGroupSeeder::class,
            PermissionSeeder::class,
            PermissionConfigSeeder::class,

            ContaStatusTipoSeeder::class,
            ServicoPagamentoLancamentoStatusTipoSeeder::class,

            ContaTipoSeeder::class,
            ContaSubtipoSeeder::class,
            AreaJuridicaSeeder::class,
            PagamentoTipoSeeder::class,
            PagamentoTipoTenantSeeder::class,
            ContaSeeder::class,
            ParticipacaoRegistroTipoSeeder::class,
            ServicoParticipacaoReferenciaTipoSeeder::class,
        ]);
    }
}
