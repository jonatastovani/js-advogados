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
        // IDs dos dominios já salvos
        $existingUserDomains = collect($userDomainsExistentes)->pluck('id')->toArray();
        // IDs enviados (exclui novos dominios sem ID)
        $submittedUserDomainsIds = collect($userDomainsEnviados)->pluck('id')->filter()->toArray();

        // Dominios ausentes no PUT devem ser excluídos
        $idsToDelete = array_diff($existingUserDomains, $submittedUserDomainsIds);

        if ($idsToDelete) {
            foreach ($idsToDelete as $id) {
                $userDomainDelete = $this->modelUserTenantDomain::withoutDomain()->find($id);
                if ($userDomainDelete) {
                    $userDomainDelete->delete();
                }
            }
        }

        foreach ($userDomainsEnviados as $userDomain) {
            $userDomainNewOrUpdate = $userDomain;

            if ($userDomain->id) {
                $userDomainNewOrUpdate = $this->modelUserTenantDomain::withoutDomain()->find($userDomain->id);
                $userDomainNewOrUpdate->fill($userDomain->toArray());
            } else {
                $userDomainNewOrUpdate->user_id = $user->id;
            }

            $userDomainNewOrUpdate->save();
        }
    }

    protected function verificacaoUserDomains(Fluent $requestData, Model $resource, Fluent $arrayErrors): Fluent
    {

        $userDomains = [];
        foreach ($requestData->user_domains as $userDomain) {
            $userDomain = new Fluent($userDomain);

            //Verifica se o id de domínio informado existe
            $validacaoDominioId = ValidationRecordsHelper::validateRecord(Domain::class, ['id' => $userDomain->domain_id]);
            if (!$validacaoDominioId->count()) {
                $arrayErrors["domain_id_{$userDomain->domain_id}"] = LogHelper::gerarLogDinamico(404, 'O domínio informado não existe.', $requestData)->error;
            } else {

                if ($resource->id) {
                    // Verifica se tem user cadastrado
                    $user = $resource->pessoa->perfil_usuario->user ?? null;

                    if (isset($user->id)) {

                        // Se tiver, verifica se já não tem acesso ao domínio
                        $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->modelUserTenantDomain::class, [
                            'user_id' => $user->id,
                            'domain_id' => $userDomain->domain_id
                        ], $userDomain->id ?? null);

                        if ($validacaoRecursoExistente->count()) {
                            $domain = Domain::withTrashed()->find($userDomain->domain_id);
                            $arrayErrors->{"dominio_{$userDomain->domain_id}"} = LogHelper::gerarLogDinamico(404, "O domínio '{$domain->domain}' já existe para esta pessoa.", $requestData)->error;
                        }
                    }
                }

                $newUserDomain = new $this->modelUserTenantDomain;
                $newUserDomain->fill($userDomain->toArray());
                array_push($userDomains, $newUserDomain);
            }
        }

        $retorno = new Fluent();
        $retorno->userDomains = $userDomains;
        $retorno->arrayErrors = $arrayErrors;

        return $retorno;
    }
}
