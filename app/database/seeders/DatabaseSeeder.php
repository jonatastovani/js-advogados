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
use Database\Seeders\Referencias\DocumentoTipoUpdateSeeder;
use Database\Seeders\Referencias\PagamentoTipoSeeder;
use Database\Seeders\Referencias\ParticipacaoRegistroTipoSeeder;
use Database\Seeders\Referencias\PessoaPerfilTipoSeeder;
use Database\Seeders\Referencias\LancamentoStatusTipoSeeder;
use Database\Seeders\Referencias\LancamentoStatusTipoUpdateSeeder;
use Database\Seeders\Referencias\MovimentacaoContaStatusTipoSeeder;
use Database\Seeders\Referencias\MovimentacaoContaStatusTipoUpdateSeeder;
use Database\Seeders\Referencias\MovimentacaoContaTipoSeeder;
use Database\Seeders\Referencias\PagamentoStatusTipoSeeder;
use Database\Seeders\Referencias\PagamentoTipoUpdateSeeder;
use Database\Seeders\Referencias\PessoaPerfilTipoUpdateSeeder;
use Database\Seeders\Tenant\DocumentoTipoTenantSeeder;
use Database\Seeders\Tenant\DocumentoTipoTenantUpdateSeeder;
use Database\Seeders\Tenant\EscolaridadeTenantSeeder;
use Database\Seeders\Tenant\EstadoCivilTenantSeeder;
use Database\Seeders\Tenant\LancamentoCategoriaTipoTenantSeeder;
use Database\Seeders\Tenant\ServicoParticipacaoTipoTenantSeeder;
use Database\Seeders\Tenant\SexoTenantSeeder;
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

                ContaTipoSeeder::class,
                ContaSubtipoSeeder::class,
                ContaStatusTipoSeeder::class,
                MovimentacaoContaStatusTipoSeeder::class,
                PagamentoStatusTipoSeeder::class,
                PagamentoTipoSeeder::class,
                LancamentoStatusTipoSeeder::class,
            ]);

            if (env('APP_ENV') == 'local') {
                $this->call([PessoaPerfilClienteSeeder::class]);
            }

            $this->call([
                AreaJuridicaTenantSeeder::class,
                ContaSeeder::class,
                DocumentoTipoTenantSeeder::class,
                EstadoCivilTenantSeeder::class,
                EscolaridadeTenantSeeder::class,
                LancamentoCategoriaTipoTenantSeeder::class,
                PagamentoTipoTenantSeeder::class,
                ServicoParticipacaoTipoTenantSeeder::class,
                SexoTenantSeeder::class,
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
                PessoaPerfilTipoUpdateSeeder::class,
                // DocumentoTipoTenantUpdateSeeder::class,
                // DocumentoTipoUpdateSeeder::class,
                // PagamentoTipoUpdateSeeder::class,
                // LancamentoStatusTipoUpdateSeeder::class,
                // MovimentacaoContaStatusTipoUpdateSeeder::class,
            ]);
        }
    }
}
