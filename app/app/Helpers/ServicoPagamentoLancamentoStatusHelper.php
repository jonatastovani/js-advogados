<?php

namespace App\Helpers;

use App\Models\Auth\Tenant;
use App\Enums\LancamentoStatusTipoEnum;
use App\Common\CommonsFunctions;
use App\Models\Servico\ServicoPagamentoLancamento;

class ServicoPagamentoLancamentoStatusHelper
{
    /**
     * Processa os lançamentos de todos os tenants, atualizando os status conforme vencimento.
     *
     * @return void
     */
    public static function processarTodosTenants(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            try {
                self::processarLancamentosPorTenant($tenant->id);
            } catch (\Throwable $e) {
                CommonsFunctions::generateLog(
                    "Erro ao processar lançamentos do Tenant ID {$tenant->id}: {$e->getMessage()}",
                    ['channel' => 'processamento_lancamento_servico']
                );
            }
        }
    }

    /**
     * Processa os lançamentos de um tenant específico, atualizando status em lote com eventos.
     *
     * @param string $tenantId
     * @return void
     */
    public static function processarLancamentosPorTenant(string $tenantId): void
    {
        try {
            self::atualizarLancamentosEmAtraso($tenantId);
            self::atualizarLancamentosInadimplentes($tenantId);
        } catch (\Throwable $e) {
            CommonsFunctions::generateLog(
                "Erro na atualização de lançamentos do Tenant ID {$tenantId}: {$e->getMessage()}",
                ['channel' => 'processamento_lancamento_servico']
            );
        }
    }

    /**
     * Atualiza os lançamentos vencidos até ontem no mês atual para status "EM_ATRASO",
     * utilizando save() para acionar eventos e logs.
     *
     * @param string $tenantId
     * @return void
     */
    private static function atualizarLancamentosEmAtraso(string $tenantId): void
    {
        $hoje = now();
        $ontem = $hoje->copy()->subDay()->toDateString();

        $statusPermitidos = LancamentoStatusTipoEnum::statusPassiveisDeSeremMarcadosComoEmAtraso();

        $count = 0;

        ServicoPagamentoLancamento::where('tenant_id', $tenantId)
            ->whereDate('data_vencimento', '<=', $ontem)
            ->whereMonth('data_vencimento', $hoje->month)
            ->whereYear('data_vencimento', $hoje->year)
            ->whereIn('status_id', $statusPermitidos)
            ->chunk(100, function ($lancamentos) use (&$count) {
                foreach ($lancamentos as $lancamento) {
                    $lancamento->status_id = LancamentoStatusTipoEnum::EM_ATRASO->value;
                    $lancamento->save();
                    $count++;
                }
            });

        CommonsFunctions::generateLog(
            "Atualização para EM_ATRASO concluída para Tenant ID {$tenantId}. Registros afetados: {$count}",
            ['channel' => 'processamento_lancamento_servico']
        );
    }

    /**
     * Atualiza os lançamentos vencidos em meses anteriores para status "INADIMPLENTE",
     * utilizando save() para acionar eventos e logs.
     *
     * @param string $tenantId
     * @return void
     */
    private static function atualizarLancamentosInadimplentes(string $tenantId): void
    {
        $inicioMesAtual = now()->startOfMonth()->toDateString();

        $statusPermitidos = LancamentoStatusTipoEnum::statusPassiveisDeSeremMarcadosComoInadimplente();

        $count = 0;

        ServicoPagamentoLancamento::where('tenant_id', $tenantId)
            ->whereDate('data_vencimento', '<', $inicioMesAtual)
            ->whereIn('status_id', $statusPermitidos)
            ->chunk(100, function ($lancamentos) use (&$count) {
                foreach ($lancamentos as $lancamento) {
                    $lancamento->status_id = LancamentoStatusTipoEnum::INADIMPLENTE->value;
                    $lancamento->save();
                    $count++;
                }
            });

        CommonsFunctions::generateLog(
            "Atualização para INADIMPLENTE concluída para Tenant ID {$tenantId}. Registros afetados: {$count}",
            ['channel' => 'processamento_lancamento_servico']
        );
    }
}
