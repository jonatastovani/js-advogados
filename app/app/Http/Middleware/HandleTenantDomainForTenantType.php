<?php

namespace App\Http\Middleware;

use App\Enums\TenantTypeEnum;
use App\Models\Auth\Domain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware responsável por gerenciar o redirecionamento e a identificação do domínio
 * para tenants do tipo ADVOCACIA_MANUAL.
 * 
 * Funcionalidades:
 * - Identifica o domínio acessado e obtém o tenant correspondente.
 * - Se o tenant exigir redirecionamento, direciona para o domínio correto.
 * - Se o tenant for do tipo que permite seleção de domínio manual, adiciona o domínio selecionado na request.
 */
class HandleTenantDomainForTenantType
{
    /**
     * Manipula a requisição verificando e aplicando o domínio correto para o tenant.
     *
     * @param  \Illuminate\Http\Request  $request  Requisição HTTP recebida
     * @param  \Closure  $next  Próximo middleware na pilha de execução
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtém o domínio acessado pelo usuário
        $currentDomain = $request->getHost();

        // Obtém o protocolo (HTTP ou HTTPS) da requisição
        // O método `secure()` retorna true para HTTPS e false para HTTP
        $protocol = $request->secure() ? 'https' : 'http';

        // Busca o domínio no banco de dados com seu relacionamento `tenant`
        $domain = Domain::with('tenant')->where('domain', $currentDomain)->first();

        // Verifica se o domínio pertence a um tenant e se ele é do tipo ADVOCACIA_MANUAL
        if ($domain && $domain->tenant && $domain->tenant->tenant_type_id == TenantTypeEnum::ADVOCACIA_MANUAL->value) {

            // Obtém o ID do domínio de redirecionamento configurado no tenant
            $idDomainRedirection = $domain->tenant->redirection_domain_id;

            if ($idDomainRedirection) {
                // Se o domínio acessado não for o correto, realiza o redirecionamento
                if ($idDomainRedirection != $domain->id) {
                    // Obtém o domínio correto para redirecionamento
                    $correctDomain = Domain::find($idDomainRedirection)->domain;

                    // Redireciona para o domínio correto mantendo a URI original da requisição
                    return redirect()->to("{$protocol}://{$correctDomain}{$request->getRequestUri()}");
                }

                // Se o tenant permite a seleção de domínio manual (por exemplo, `tenant_type_id = 4`), captura essa informação
                $selectedDomain = $request->header(config('tenancy_custom.tenant_type.header_attribute_key')) ?? 0;

                // Adiciona a chave `tenant_domain_selected_id` à request para que possa ser utilizada posteriormente
                $request->merge([config('tenancy_custom.tenant_type.name_attribute_key') => $selectedDomain]);
            }
        }

        // Prossegue com a execução da requisição
        return $next($request);
    }
}
