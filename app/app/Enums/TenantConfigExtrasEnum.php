<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum TenantConfigExtrasEnum: string
{
    use EnumTrait;

/**
     * Perfis permitidos para participação nos ressarcimentos
     */
    case PERFIS_PERMITIDO_PARTICIPACAO_RESSARCIMENTO = 'perfis_permitido_participacao_ressarcimento';
    
    /**
     * Perfis permitidos para participação nos serviços
     */
    case PERFIS_PERMITIDO_PARTICIPACAO_SERVICO = 'perfis_permitido_participacao_servico';
}
