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
use Database\Seeders\Referencias\LancamentoStatusTipoSeeder;
use Database\Seeders\Referencias\LancamentoStatusTipoUpdateSeeder;
use Database\Seeders\Referencias\MovimentacaoContaStatusTipoSeeder;
use Database\Seeders\Referencias\MovimentacaoContaStatusTipoUpdateSeeder;
use Database\Seeders\Referencias\MovimentacaoContaTipoSeeder;
use Database\Seeders\Referencias\PagamentoStatusTipoSeeder;
use Database\Seeders\Tenant\LancamentoCategoriaTipoTenantSeeder;
use Database\Seeders\Tenant\ServicoParticipacaoTipoTenantSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (1 == 1) {
            $this->call([
                TenantTypeSeeder::class,
                TenantDomainSeeder::class,
                UserSeeder::class,
                UserTenantDomainSeeder::class,

                PessoaPerfilTipoSeeder::class,
                DocumentoTipoSeeder::class,

                PermissionModuleSeeder::class,
                PermissionGroupSeeder::class,
                PermissionSeeder::class,
                PermissionConfigSeeder::class,

                ContaStatusTipoSeeder::class,
                MovimentacaoContaStatusTipoSeeder::class,
                PagamentoStatusTipoSeeder::class,
                LancamentoStatusTipoSeeder::class,
            ]);

            if (env('APP_ENV') == 'local') {
                $this->call([PessoaPerfilClienteSeeder::class]);
            }

            $this->call([
                ContaTipoSeeder::class,
                ContaSubtipoSeeder::class,
                AreaJuridicaTenantSeeder::class,
                PagamentoTipoSeeder::class,
                PagamentoTipoTenantSeeder::class,
                ServicoParticipacaoTipoTenantSeeder::class,
                ContaSeeder::class,
                LancamentoCategoriaTipoTenantSeeder::class,
            ]);

            if (env('APP_ENV') == 'local') {
                $this->call([
                    PessoaPerfilParceiroSeeder::class,
                    PessoaPerfilParceiroClienteSeeder::class,
                ]);
            }

            $this->call([
                ParticipacaoRegistroTipoSeeder::class,
                MovimentacaoContaTipoSeeder::class,
            ]);

        } else {

            $this->call([
                LancamentoStatusTipoUpdateSeeder::class,
                MovimentacaoContaStatusTipoUpdateSeeder::class,
            ]);
        }
    }
}
