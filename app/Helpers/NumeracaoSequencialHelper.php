<?php

namespace App\Helpers;

use App\Models\Referencias\NumeracaoSequencial;
use Illuminate\Support\Facades\DB;

class NumeracaoSequencialHelper
{
    /**
     * Obtém o próximo número sequencial sem atualizar o último número.
     *
     * @param string $tipo O tipo de numeração (ex: 'servico')
     * @param string $tenantId O ID do tenant atual
     * @return array O próximo número sequencial e o registro associado
     */
    public static function obterProximoNumero($tipo, $tenantId)
    {
        $anoAtual = now()->year;

        // Inicia uma transação para garantir a exclusividade
        return DB::transaction(function () use ($tenantId, $anoAtual, $tipo) {
            // Bloqueia a linha até que o número seja gerado
            $numeracao = NumeracaoSequencial::where([
                'tenant_id' => $tenantId,
                'ano' => $anoAtual,
                'tipo' => $tipo,
            ])->lockForUpdate()->first();

            // Se a numeração ainda não existe, cria um novo registro
            if (!$numeracao) {
                $numeracao = NumeracaoSequencial::create([
                    'tenant_id' => $tenantId,
                    'ano' => $anoAtual,
                    'tipo' => $tipo,
                    'ultimo_numero' => 0,
                ]);
            }

            // Não incrementa ainda, apenas retorna o próximo número potencial
            $novoNumero = $numeracao->ultimo_numero + 1;

            return [
                'numero' => sprintf('%04d/%d', $novoNumero, $anoAtual),
                'registroNumeracao' => $numeracao, // Retorna o registro para ser atualizado mais tarde
            ];
        }, 5); // Tempo máximo para manter o lock
    }

    /**
     * Confirma a numeração sequencial atualizando o último número no registro.
     *
     * @param NumeracaoSequencial $registroNumeracao O registro da numeração a ser atualizado
     * @return void
     */
    public static function confirmarNumeracao(NumeracaoSequencial $registroNumeracao)
    {
        DB::transaction(function () use ($registroNumeracao) {
            // Incrementa o último número e atualiza
            $registroNumeracao->ultimo_numero++;
            $registroNumeracao->save();
        });
    }
}
