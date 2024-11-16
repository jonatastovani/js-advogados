<?php

namespace App\Helpers;

use App\Enums\MovimentacaoContaTipoEnum;
use App\Models\Financeiro\MovimentacaoConta;
use Illuminate\Support\Facades\DB;

class AtualizacaoSaldoContaHelper
{
    public static function inserirMovimentacao($dados): MovimentacaoConta
    {
        return DB::transaction(function () use ($dados) {
            $ultimoSaldo = MovimentacaoConta::where('conta_id', $dados['conta_id'])
                ->orderBy('data_movimentacao', 'desc')
                ->lockForUpdate()
                ->value('saldo_atualizado') ?? 0;

            switch ($dados['movimentacao_tipo_id']) {
                case MovimentacaoContaTipoEnum::CREDITO->value:
                    $novoSaldo = $ultimoSaldo + $dados['valor_movimentado'];
                    break;
                case MovimentacaoContaTipoEnum::DEBITO->value:
                    $novoSaldo = $ultimoSaldo - $dados['valor_movimentado'];
                    break;
                case MovimentacaoContaTipoEnum::AJUSTE->value:
                    $novoSaldo = $dados['valor_movimentado'];
                    break;
                default:
                    throw new \InvalidArgumentException('Tipo de movimentação inválido.');
            }

            $dados['saldo_atualizado'] = $novoSaldo;

            return MovimentacaoConta::create($dados);
        });
    }
}
