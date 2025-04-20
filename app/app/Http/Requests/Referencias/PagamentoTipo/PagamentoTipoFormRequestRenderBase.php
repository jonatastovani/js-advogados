<?php

namespace App\Http\Requests\Referencias\PagamentoTipo;

use App\Enums\PagamentoTipoEnum;
use App\Http\Requests\BaseFormRequest;
use App\Models\Referencias\PagamentoTipo;

class PagamentoTipoFormRequestRenderBase extends BaseFormRequest
{
    public function rules(): array
    {
        return ['forma_pagamento_id' => 'required|uuid'];
    }

    protected function buildRulesFromTipoPagamento(int $tipoPagamentoId): array
    {
        $pagamentoTipo = PagamentoTipo::findOrFail($tipoPagamentoId);
        $rules = $this->rules(); // pega as regras básicas, como forma_pagamento_id

        foreach ($pagamentoTipo->campos_obrigatorios as $value) {

            $rulesPagamento = $value['form_request_rule_helper'] ?? $value['form_request_rule'];

            // Se for entrada com parcelamento ou parcelado e o campo for valor_total, aplica a lógica dinâmica
            if (
                in_array($tipoPagamentoId, [
                    PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO->value,
                    PagamentoTipoEnum::PARCELADO->value
                ]) &&
                $value['nome'] === 'valor_total'
            ) {
                $entradaValor = request('entrada_valor') ?? 0;
                $quantidadeParcelas = request('parcela_quantidade') ?? 0;
                $minimo = (float) $entradaValor + ((int) $quantidadeParcelas * 1);

                $rulesPagamento = str_replace('min:1', 'min:' . $minimo, $rulesPagamento);
            }

            $rules[$value['nome']] = $rulesPagamento;
        }

        return $rules;
    }

    protected function customMessages(): array
    {
        return [
            'valor_total.min' => 'O campo :attribute deve ser no mínimo :min.',
            'entrada_valor.min' => 'O campo :attribute deve ser no mínimo :min.',
            'parcela_valor.min' => 'O campo :attribute deve ser no mínimo :min.',
        ];
    }

    protected function customAttributeNames(): array
    {
        return [
            'valor_total' => 'valor total',
            'entrada_valor' => 'valor de entrada',
            'parcela_valor' => 'valor da parcela',
            'parcela_mes_ano_inicio' => 'mês/ano de início',
        ];
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($validator->failed() as $field => $rules) {
                foreach ($rules as $rule => $parameters) {
                    if ($rule === 'Min' && in_array($field, ['valor_total', 'entrada_valor', 'parcela_valor'])) {
                        // Formatar o valor mínimo para moeda e substituir :attribute e :min
                        $formattedMin = number_format($parameters[0], 2, ',', '.');
                        $attributeName = $this->attributes()[$field] ?? $field;
                        $message = str_replace(
                            [':attribute', ':min'],
                            [$attributeName, 'R$ ' . $formattedMin],
                            $this->customMessages()["{$field}.min"]
                        );
                        // Limpar erros anteriores e adicionar mensagem formatada
                        $validator->errors()->forget($field);
                        $validator->errors()->add($field, $message);
                    }
                }
            }
        });
    }
}
