<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum TenantConfigExtrasEnum: string
{
    use EnumTrait;

    /**
     * Tipo de tenant, chave estrangeira e enums no Enum: App\Enums\TenantTypeEnum
     */
    case TENANT_TYPE_ID = 'tenant_type_id';

    /**
     * Perfis permitidos para participação nos ressarcimentos
     */
    case PERFIS_PERMITIDO_PARTICIPACAO_RESSARCIMENTO = 'perfis_permitido_participacao_ressarcimento';
    
    /**
     * Perfis permitidos para participação nos serviços
     */
    case PERFIS_PERMITIDO_PARTICIPACAO_SERVICO = 'perfis_permitido_participacao_servico';
    
    /**
     * Ordenação por status dos lançamentos de serviço na tela de Editar Serviço, dentro do Painel de Pagamentos
     */
    case ORDER_BY_SERVICOS_LANCAMENTOS_EDICAO_ARRAY = 'order_by_servicos_lancamentos_edicao_array';

    /**
     * Ordenação por status dos lançamentos de serviço na tela de Listagem de Receitas
     */
    case ORDER_BY_SERVICOS_LANCAMENTOS_LISTAGEM_ARRAY = 'order_by_servicos_lancamentos_listagem_array';
}
