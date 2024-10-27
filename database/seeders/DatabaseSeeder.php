<?php

namespace Database\Seeders;

use Database\Seeders\Financeiro\ContaSeeder;
use Database\Seeders\Pessoa\PessoaPerfilClienteSeeder;
use Database\Seeders\Pessoa\PessoaPerfilParceiroSeeder;
use Database\Seeders\Pessoa\PessoaPerfilParceiroClienteSeeder;
use Database\Seeders\Tenant\PagamentoTipoTenantSeeder;
use Database\Seeders\Tenant\AreaJuridicaTenantSeeder;
use Database\Seeders\Referencias\ContaStatusTipoSeeder;
use Database\Seeders\Referencias\ContaSubtipoSeeder;
use Database\Seeders\Referencias\ContaTipoSeeder;
use Database\Seeders\Referencias\DocumentoTipoSeeder;
use Database\Seeders\Referencias\PagamentoTipoSeeder;
use Database\Seeders\Referencias\ParticipacaoRegistroTipoSeeder;
use Database\Seeders\Referencias\PessoaPerfilTipoSeeder;
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
        // $this->call([
        //     TenantTypeSeeder::class,
        //     TenantDomainSeeder::class,
        //     UserSeeder::class,
        //     UserTenantDomainSeeder::class,

        //     PessoaPerfilTipoSeeder::class,
        //     DocumentoTipoSeeder::class,

        //     PermissionModuleSeeder::class,
        //     PermissionGroupSeeder::class,
        //     PermissionSeeder::class,
        //     PermissionConfigSeeder::class,

        //     ContaStatusTipoSeeder::class,
        //     ServicoPagamentoLancamentoStatusTipoSeeder::class,
        // ]);

        // if (env('APP_ENV') == 'local') {
        //     $this->call([PessoaPerfilClienteSeeder::class]);
        // }

        // $this->call([
        //     ContaTipoSeeder::class,
        //     ContaSubtipoSeeder::class,
        //     AreaJuridicaTenantSeeder::class,
        //     PagamentoTipoSeeder::class,
        //     PagamentoTipoTenantSeeder::class,
        //     ContaSeeder::class,
        // ]);

        // if (env('APP_ENV') == 'local') {
        //     $this->call([
        //         // PessoaPerfilParceiroSeeder::class,
        //         PessoaPerfilParceiroClienteSeeder::class,
        //     ]);
        // }

        $this->call([
            ParticipacaoRegistroTipoSeeder::class,
            // ServicoParticipacaoReferenciaTipoSeeder::class,
        ]);
    }
}
