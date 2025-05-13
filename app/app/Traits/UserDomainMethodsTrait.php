<?php

namespace App\Traits;

use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Auth\Domain;
use App\Models\Auth\User;
use App\Models\Pessoa\PessoaFisica;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

trait UserDomainMethodsTrait
{

    public function atualizarUserDomainsEnviados(PessoaFisica $resource, $userDomainsExistentes, $userDomainsEnviados, User $user, $options = [])
    {
        // IDs dos domínios já salvos
        $existingUserDomains = collect($userDomainsExistentes)->pluck('id')->toArray();
        // IDs enviados (exclui novos domínios sem ID)
        $submittedUserDomainsIds = collect($userDomainsEnviados)->pluck('id')->filter()->toArray();

        // Não vamos excluir mais, vamos somente inativar
        // // Domínios ausentes no PUT devem ser excluídos
        // $idsToDelete = array_diff($existingUserDomains, $submittedUserDomainsIds);

        // if ($idsToDelete) {
        //     foreach ($idsToDelete as $id) {
        //         $userDomainDelete = $this->modelUserTenantDomain::withoutDomain()->find($id);
        //         if ($userDomainDelete) {
        //             $userDomainDelete->delete();
        //         }
        //     }
        // }

        foreach ($userDomainsEnviados as $userDomain) {
            $userDomainNewOrUpdate = $userDomain;

            if ($userDomain->id) {
                // Encontrar o domínio existente para atualização
                $userDomainNewOrUpdate = $this->modelUserTenantDomain::withoutDomain()->find($userDomain->id);

                // Atualiza apenas o campo ativo_bln se ele estiver presente nos dados enviados
                if (isset($userDomain->ativo_bln)) {
                    $userDomainNewOrUpdate->ativo_bln = $userDomain->ativo_bln;
                }
            } else {
                // Criação de um novo domínio para o usuário
                $userDomainNewOrUpdate->user_id = $user->id;
            }

            // Salvar o registro atualizado ou novo
            $userDomainNewOrUpdate->save();
        }
    }

    protected function verificacaoUserDomains(Fluent $requestData, Model $resource, Fluent $arrayErrors): Fluent
    {
        $userDomains = [];

        foreach ($requestData->user_domains as $userDomain) {
            $userDomain = new Fluent($userDomain);

            // Verificação da existência do domínio
            $validacaoDominioId = ValidationRecordsHelper::validateRecord(Domain::class, ['id' => $userDomain->domain_id]);
            if (!$validacaoDominioId->count()) {
                $arrayErrors["domain_id_{$userDomain->domain_id}"] = LogHelper::gerarLogDinamico(404, "O domínio informado '{$userDomain->domain_id}' não existe.", $requestData)->error;
                continue;
            }

            if ($resource->id) {
                // Obter o usuário associado à pessoa
                $user = $resource->pessoa->perfil_usuario->user ?? null;

                if (isset($user->id)) {
                    // Verificar se o usuário já possui acesso ao domínio
                    $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente(
                        $this->modelUserTenantDomain::class,
                        ['user_id' => $user->id, 'domain_id' => $userDomain->domain_id],
                        $userDomain->id ?? null
                    );

                    if ($validacaoRecursoExistente->count()) {
                        $domain = Domain::withTrashed()->find($userDomain->domain_id);
                        $arrayErrors->{"dominio_{$userDomain->domain_id}"} = LogHelper::gerarLogDinamico(404, "O domínio '{$domain->domain}' já existe para esta pessoa.", $requestData)->error;
                        continue;
                    }
                }
            }

            // Criar ou atualizar o domínio do usuário
            $newUserDomain = new $this->modelUserTenantDomain;
            $newUserDomain->fill($userDomain->toArray());
            array_push($userDomains, $newUserDomain);
        }

        $retorno = new Fluent();
        $retorno->userDomains = $userDomains;
        $retorno->arrayErrors = $arrayErrors;

        return $retorno;
    }
}
