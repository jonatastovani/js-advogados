<?php

namespace App\Helpers;

use App\Enums\TenantTypeEnum;
use App\Models\Auth\UserTenantDomain;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class TenantTypeDomainCustomHelper
{
    /**
     * Armazena os domínios do usuário para evitar múltiplas consultas na mesma requisição.
     *
     * @var Collection|null
     */
    protected static ?Collection $cachedDomains = null;

    /**
     * Obtém a lista de domínios acessíveis pelo usuário autenticado.
     * O resultado é armazenado estaticamente para reutilização na mesma requisição.
     *
     * @param array $options - Parâmetros adicionais:
     *                         - 'force_refresh' (bool) → Se true, força a atualização da lista de domínios.
     * @return Collection Lista de domínios acessíveis.
     */
    public static function getDomainsPorUsuario(array $options = []): Collection
    {
        // Se já existe um cache e não foi requisitada uma atualização, retorna o cache
        if (self::$cachedDomains !== null && empty($options['force_refresh'])) {
            return self::$cachedDomains;
        }

        // Obtém os domínios do usuário logado
        $acessos = UserTenantDomain::withoutDomain()->with('domain')
            ->where('user_id', Auth::id())
            ->get();

        // Armazena na variável estática
        return self::$cachedDomains = $acessos->pluck('domain');
    }

    /**
     * Obtém o nome do domínio selecionado pelo usuário.
     *
     * @return string Nome do domínio ou vazio se nenhum for selecionado.
     */
    public static function getDomainNameSelected(): string
    {
        switch (tenant('tenant_type_id')) {
            case TenantTypeEnum::ADVOCACIA_MANUAL->value:
                $selectedDomainId = Request::get(config('tenancy_custom.tenant_type.name_attribute_key'));

                return $selectedDomainId ? DomainTenantResolver::$currentDomain->name : '';

            default:
                return DomainTenantResolver::$currentDomain->name;
        }
    }

    /**
     * Verifica se o tenant está configurado para identificação manual do domínio.
     *
     * @return bool True se for ADVOCACIA_MANUAL, false caso contrário.
     */
    public static function getDomainCustomBln(): bool
    {
        return tenant('tenant_type_id') == TenantTypeEnum::ADVOCACIA_MANUAL->value;
    }

    /**
     * Define o ID do domínio selecionado na request.
     *
     * @param mixed $domainId ID do domínio selecionado.
     */
    public static function setDomainSelectedInAttributeKey($domainId): void
    {
        Request::merge([config('tenancy_custom.tenant_type.name_attribute_key') => $domainId]);
    }

    /**
     * Obtém o ID do domínio selecionado armazenado na request.
     *
     * @return mixed ID do domínio selecionado ou null se não existir.
     */
    public static function getDomainIdSelectedInAttributeKey()
    {
        return Request::get(config('tenancy_custom.tenant_type.name_attribute_key'));
    }

    /**
     * Obtém os domínios que devem ser aplicados nos filtros de escopo para a inserção de registros.
     * 
     * Para tenants com identificação manual de domínio, verifica se há um domínio selecionado.
     * Se nenhum domínio específico for selecionado, retorna todos os domínios acessíveis pelo usuário.
     * Caso contrário, retorna apenas o domínio atual do tenant.
     *
     * @return array Lista de IDs dos domínios a serem considerados no escopo.
     */
    public static function getDominiosInserirScopeDomain(): array
    {
        if (tenant('tenant_type_id') === TenantTypeEnum::ADVOCACIA_MANUAL->value) {
            // Obtém o ID do domínio selecionado
            $selectedDomainId = self::getDomainIdSelectedInAttributeKey();

            // Se um domínio foi selecionado, retorna apenas ele
            if (!empty($selectedDomainId)) {
                return [$selectedDomainId];
            }

            // Caso contrário, retorna todos os domínios acessíveis ao usuário
            return self::getDomainsPorUsuario()->pluck('id')->toArray();
        }

        // Para outros tipos de tenants, retorna apenas o domínio atual
        return [DomainTenantResolver::$currentDomain->id];
    }
}
