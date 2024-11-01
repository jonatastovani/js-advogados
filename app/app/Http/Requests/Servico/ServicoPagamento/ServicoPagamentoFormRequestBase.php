<?php

namespace App\Http\Requests\Servico\ServicoPagamento;

use App\Common\RestResponse;
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
            'conta_id' => 'required|uuid',
            'observacao' => 'nullable|string',
        ];

        // Obtém o valor de 'pagamento_tipo_tenant_id' da requisição
        $pagamentoTipoTenantId = $this->input('pagamento_tipo_tenant_id');

        if (!$pagamentoTipoTenantId) {
            $log = LogHelper::gerarLogDinamico('404', 'Tipo de Pagamento do Tenant não informado. Consulte o desenvolvedor.', $this);
            return RestResponse::createErrorResponse(404, $log->error, $log->trace_id)->throwResponse();
        }

        $consulta =  PagamentoTipoTenant::with('pagamento_tipo')->find($pagamentoTipoTenantId);

        if (!$consulta) {
            return RestResponse::createErrorResponse(404, 'Tipo de Pagamento do Tenant não encontrado.')->throwResponse();
        }

        $pagamentoTipo = $consulta->pagamento_tipo;

        // Define as regras de acordo com o tipo de pagamento
        foreach ($pagamentoTipo->configuracao['campos_obrigatorios'] as $value) {
            $rules[$value['nome']] = $value['formRequestRule'];
        }

        return $rules;
    }

    protected function customAttributeNames(): array
    {
        return [
            'pagamento_tipo_tenant_id' => 'tipo de pagamento',
            'conta_id' => 'conta',
            'valor_total' => 'valor total',
            'entrada_valor' => 'valor de entrada',
            'entrada_data' => 'data de entrada',
            'parcela_data_inicio' => 'primeira parcela',
            'parcela_quantidade' => 'quantidade de parcelas',
            'parcela_vencimento_dia' => 'dia de vencimento',
            'parcela_valor' => 'valor da parcela',
            'descricao_condicionado' => 'descrição condicionada',
            'observacao' => 'observação',
        ];
    }
}
