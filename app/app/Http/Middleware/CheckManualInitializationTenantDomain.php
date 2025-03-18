<?php

namespace App\Http\Middleware;

use App\Enums\TenantTypeEnum;
use App\Helpers\TenantTypeDomainCustomHelper;
use App\Models\Auth\Domain;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\View;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

/**
 * Middleware responsável por validar a inicialização manual do domínio para certos tipos de tenants.
 *
 * Funcionalidades:
 * - Verifica se o tenant exige inicialização manual do domínio (`tenant_type_id = X`).
 * - Obtém o ID do domínio selecionado a partir da variável global.
 * - Se não houver um domínio selecionado, retorna uma página de erro.
 */
class CheckManualInitializationTenantDomain
{
    /**
     * Manipula a requisição verificando a configuração de domínio para tenants de inicialização manual.
     *
     * @param  \Illuminate\Http\Request  $request  Requisição HTTP recebida.
     * @param  \Closure  $next  Próximo middleware na pilha de execução.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o domínio pertence a um tenant e se ele exige inicialização manual
        if (tenant('tenant_type_id') == TenantTypeEnum::ADVOCACIA_MANUAL->value) {

            // Obtém o ID do domínio de redirecionamento configurado no tenant
            $idDomainRedirection = tenant('redirection_domain_id');

            if ($idDomainRedirection) {

                $domain = DomainTenantResolver::$currentDomain;

                // Se o domínio acessado não for o correto, realiza o redirecionamento
                if ($idDomainRedirection != $domain->id) {

                    // Obtém o domínio correto para redirecionamento
                    $correctDomain = Domain::find($idDomainRedirection)->domain;

                    // Obtém o protocolo (HTTP ou HTTPS) da requisição
                    // O método `secure()` retorna true para HTTPS e false para HTTP
                    $protocol = $request->secure() ? 'https' : 'http';

                    // Log::debug('Redirecionamento manual', [
                    //     'protocol' => $protocol,
                    //     'correctDomain' => $correctDomain,
                    //     'originalUri' => $request->getRequestUri(),
                    //     'fullUrl' => $request->fullUrl(),
                    // ]);

                    // Redireciona para o domínio correto mantendo a URI original da requisição
                    return redirect()->to("{$protocol}://{$correctDomain}{$request->getRequestUri()}");
                }

                $nameAttributeKey = config('tenancy_custom.tenant_type.name_attribute_key');
                $headerAttributeKey = config('tenancy_custom.tenant_type.header_attribute_key');

                // Captura a informação do domínio selecionado na chave esperada
                $selectedDomainId = $request->header($headerAttributeKey) ?? 0;

                if (!$selectedDomainId) {
                    // Se nenhum id de domínio foi enviado pelo header, então se procura pelo input
                    if ($request->has($nameAttributeKey)) {
                        $selectedDomainId = $request->input($nameAttributeKey);
                    }
                }
                // Adiciona a chave `tenant_domain_selected_id` à request para que possa ser utilizada posteriormente
                TenantTypeDomainCustomHelper::setDomainSelectedInAttributeKey($selectedDomainId);

                // Se o id selecionado for diferente do domínio atual (que inicialmente é o domínio padrão de acesso), resolve o domínio
                if ($selectedDomainId != $domain->id) {
                    $domain = tenant()->domains->where('id', $selectedDomainId)->first();

                    // Se houver um domínio selecionado, resolve o domínio
                    if ($selectedDomainId) {
                        app(DomainTenantResolver::class)->resolved(tenant(), $domain->domain);
                    }
                }
            }
        }

        // Prossegue com a execução da requisição
        return $next($request);
    }
}
