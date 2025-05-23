<?php

namespace Database\Seeders;

use Database\Seeders\Auth\DomainAdminSeeder;
use Database\Seeders\Auth\DomainEscritorioSeeder;
use Database\Seeders\Auth\PermissionConfigSeeder;
use Database\Seeders\Auth\PermissionGroupSeeder;
use Database\Seeders\Auth\PermissionModuleSeeder;
use Database\Seeders\Auth\PermissionSeeder;
use Database\Seeders\Auth\TenantAdminSeeder;
use Database\Seeders\Auth\TenantEscritorioSeeder;
use Database\Seeders\Auth\TenantTypeSeeder;
use Database\Seeders\Auth\UserEscritorioSeeder;
use Database\Seeders\Auth\UserSeeder;
use Database\Seeders\Auth\UserTenantDomainSeeder;
use Database\Seeders\Pessoa\PessoaPerfilClienteSeeder;
use Database\Seeders\Pessoa\PessoaPerfilEmpresaSeeder;
use Database\Seeders\Pessoa\PessoaPerfilParceiroSeeder;
use Database\Seeders\Pessoa\PessoaPerfilParceiroClienteSeeder;
use Database\Seeders\Referencias\ChavePixTipoSeeder;
use Database\Seeders\Tenant\PagamentoTipoTenantSeeder;
use Database\Seeders\Tenant\AreaJuridicaTenantSeeder;
use Database\Seeders\Referencias\ContaStatusTipoSeeder;
use Database\Seeders\Referencias\ContaSubtipoSeeder;
use Database\Seeders\Referencias\ContaTipoSeeder;
use Database\Seeders\Referencias\DocumentoGeradoTipoSeeder;
use Database\Seeders\Referencias\DocumentoModeloTipoSeeder;
use Database\Seeders\Referencias\DocumentoTipoSeeder;
use Database\Seeders\Referencias\DocumentoTipoUpdateSeeder;
use Database\Seeders\Referencias\PagamentoTipoSeeder;
use Database\Seeders\Referencias\ParticipacaoRegistroTipoSeeder;
use Database\Seeders\Referencias\PessoaPerfilTipoSeeder;
use Database\Seeders\Referencias\LancamentoStatusTipoSeeder;
use Database\Seeders\Referencias\LancamentoStatusTipoUpdateSeeder;
use Database\Seeders\Referencias\MovimentacaoContaParticipanteStatusTipoSeeder;
use Database\Seeders\Referencias\MovimentacaoContaStatusTipoSeeder;
use Database\Seeders\Referencias\MovimentacaoContaStatusTipoUpdateSeeder;
use Database\Seeders\Referencias\MovimentacaoContaTipoSeeder;
use Database\Seeders\Referencias\MovimentacaoContaTipoUpdateSeeder;
use Database\Seeders\Referencias\PagamentoStatusTipoSeeder;
use Database\Seeders\Referencias\PagamentoTipoUpdateSeeder;
use Database\Seeders\Referencias\PessoaPerfilTipoUpdateSeeder;
use Database\Seeders\Tenant\ContaTenantSeeder;
use Database\Seeders\Tenant\DocumentoTipoTenantSeeder;
use Database\Seeders\Tenant\DocumentoTipoTenantUpdateSeeder;
use Database\Seeders\Tenant\EscolaridadeTenantSeeder;
use Database\Seeders\Tenant\EstadoCivilTenantSeeder;
use Database\Seeders\Tenant\FormaPagamentoTenantSeeder;
use Database\Seeders\Tenant\LancamentoCategoriaTipoTenantSeeder;
use Database\Seeders\Tenant\ParticipacaoTipoTenantObrigatorioSeeder;
use Database\Seeders\Tenant\ParticipacaoTipoTenantSeeder;
use Database\Seeders\Tenant\SexoTenantSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (1 == 2) {
            $this->call([
                TenantTypeSeeder::class,
                TenantAdminSeeder::class,
                DomainAdminSeeder::class,
            ]);

            // Criação do Tenant e Domínio do Escritório
            $this->call([
                TenantEscritorioSeeder::class,
                DomainEscritorioSeeder::class,
            ]);

            $this->call([
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
                MovimentacaoContaParticipanteStatusTipoSeeder::class,
                PagamentoStatusTipoSeeder::class,
                PagamentoTipoSeeder::class,
                LancamentoStatusTipoSeeder::class,
                DocumentoGeradoTipoSeeder::class,
                ParticipacaoRegistroTipoSeeder::class,
                MovimentacaoContaTipoSeeder::class,
                ChavePixTipoSeeder::class,
            ]);

            $this->call([
                AreaJuridicaTenantSeeder::class,
                ContaTenantSeeder::class,
                DocumentoTipoTenantSeeder::class,
                EstadoCivilTenantSeeder::class,
                EscolaridadeTenantSeeder::class,
                LancamentoCategoriaTipoTenantSeeder::class,
                PagamentoTipoTenantSeeder::class,
                ParticipacaoTipoTenantSeeder::class,
                ParticipacaoTipoTenantObrigatorioSeeder::class,
                SexoTenantSeeder::class,
                FormaPagamentoTenantSeeder::class,
                DocumentoModeloTipoSeeder::class,
            ]);

            if (env('APP_ENV') == 'local') {
                $this->call([
                    PessoaPerfilClienteSeeder::class,
                    PessoaPerfilEmpresaSeeder::class,
                    UserEscritorioSeeder::class,
                ]);
            } else {
                $this->call([
                    PessoaPerfilEmpresaSeeder::class,
                    UserEscritorioSeeder::class,
                ]);
            }
        } else {

            $this->call([
                DocumentoTipoSeeder::class,
                DocumentoTipoTenantSeeder::class,
                ChavePixTipoSeeder::class,
                PagamentoTipoSeeder::class,
                // PessoaPerfilTipoSeeder::class,
            ]);
        }
    }
}
