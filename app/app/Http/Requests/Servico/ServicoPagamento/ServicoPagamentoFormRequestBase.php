<?php

namespace App\Http\Requests\Servico\ServicoPagamento;

use App\Common\RestResponse;
use App\Enums\PagamentoTipoEnum;
use App\Helpers\LogHelper;
use App\Http\Requests\BaseFormRequest;
use App\Models\Tenant\PagamentoTipoTenant;
use Illuminate\Support\Fluent;

class ServicoPagamentoFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        // Define as regras básicas
        $rules = [
            'pagamento_tipo_tenant_id' => 'required|uuid',
            'forma_pagamento_id' => 'required|uuid',
            'observacao' => 'nullable|string',
            'status_id' => 'nullable|integer',
            'resetar_pagamento_bln' => 'nullable|boolean',
        ];

        $verificaPagamentoTipoTenant = function ($pagamentoTipoTenantId) {

            if (!$pagamentoTipoTenantId) {
                $log = LogHelper::gerarLogDinamico('404', 'Tipo de Pagamento do Tenant não informado. Consulte o desenvolvedor.', $this);
                return RestResponse::createErrorResponse(404, $log->error, $log->trace_id)->throwResponse();
            }

            $consulta =  PagamentoTipoTenant::with('pagamento_tipo')->find($pagamentoTipoTenantId);

            if (!$consulta) {
                return RestResponse::createErrorResponse(404, 'Tipo de Pagamento do Tenant não encontrado.')->throwResponse();
            }
            return $consulta;
        };

        // Somente se for POST ou se for PUT e o campo 'resetar_pagamento_bln' for true. Caso contrário, depois de cadastrado, esses campos não se alterarão
        if ($this->isMethod('post') || (
            $this->isMethod('put') && $this->has('resetar_pagamento_bln') && $this->input('resetar_pagamento_bln') == true
        )) {

            // Obtém o valor de 'pagamento_tipo_tenant_id' da requisição
            $consulta = $verificaPagamentoTipoTenant($this->input('pagamento_tipo_tenant_id'));
            $pagamentoTipo = $consulta->pagamento_tipo;

            // Define as regras de acordo com o tipo de pagamento
            foreach ($pagamentoTipo->campos_obrigatorios as $value) {
                switch ($pagamentoTipo->id) {
                    case PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO->value:
                        if ($value['nome'] == 'valor_total') {
                            $value['form_request_rule'] = str_replace('min:0.01', "min:" . (request('parcela_quantidade') * 0.01) + request('entrada_valor'), $value['form_request_rule']);
                        }
                        break;

                    case PagamentoTipoEnum::PARCELADO->value:
                        if ($value['nome'] == 'valor_total') {
                            $value['form_request_rule'] = str_replace('min:0.01', "min:" . (request('parcela_quantidade') * 0.01), $value['form_request_rule']);
                        }
                }
                $rules[$value['nome']] = $value['form_request_rule'];
            }
        } else {

            // Obtém o valor de 'pagamento_tipo_tenant_id' da requisição
            $consulta = $verificaPagamentoTipoTenant($this->input('pagamento_tipo_tenant_id'));
            $pagamentoTipo = $consulta->pagamento_tipo;

            if ($pagamentoTipo->id == PagamentoTipoEnum::CONDICIONADO->value) {

                // Define as regras de acordo com o tipo de pagamento
                foreach ($pagamentoTipo->configuracao['campos_obrigatorios'] as $value) {
                    $rules[$value['nome']] = $value['form_request_rule'];
                }
            }
        }

        return $rules;
    }

    protected function customAttributeNames(): array
    {
        return [
            'pagamento_tipo_tenant_id' => 'tipo de pagamento',
            'forma_pagamento_id' => 'forma de pagamento',
            'valor_total' => 'valor total',
            'entrada_valor' => 'valor de entrada',
            'entrada_data' => 'data de entrada',
            'parcela_data_inicio' => 'primeira parcela',
            'parcela_quantidade' => 'quantidade de parcelas',
            'parcela_vencimento_dia' => 'dia de vencimento',
            'parcela_valor' => 'valor da parcela',
            'descricao_condicionado' => 'descrição condicionada',
            'observacao' => 'observação',
            'status_id' => 'status',
        ];
    }

    protected function customMessages(): array
    {
        return [
            'valor_total.min' => 'O campo :attribute deve ser no mínimo :min.',
            'entrada_valor.min' => 'O campo :attribute deve ser no mínimo :min.',
            'parcela_valor.min' => 'O campo :attribute deve ser no mínimo :min.',
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
