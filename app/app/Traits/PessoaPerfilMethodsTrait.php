<?php

namespace App\Traits;

use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Referencias\PessoaPerfilTipo;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;

trait PessoaPerfilMethodsTrait
{

    public function atualizarPerfisEnviados($resource, $perfisExistentes, $perfisEnviados, $options = [])
    {
        // Somente salva os novos perfis
        foreach ($perfisEnviados as $perfil) {
            if (!$perfil->id) {
                $perfil->pessoa_id = $resource->pessoa->id;
                $perfil->save();
            }
        }
    }

    protected function verificacaoPerfis(Fluent $requestData, Model $resource, Fluent $arrayErrors): Fluent
    {
        $perfis = [];
        foreach ($requestData->perfis as $perfil) {
            $perfil = new Fluent($perfil);

            //Verifica se o tipo de registro de participação informado existe
            $validacaoPerfilTipoId = ValidationRecordsHelper::validateRecord(PessoaPerfilTipo::class, ['id' => $perfil->perfil_tipo_id]);
            if (!$validacaoPerfilTipoId->count()) {
                $arrayErrors["perfil_tipo_id_{$perfil->perfil_tipo_id}"] = LogHelper::gerarLogDinamico(404, 'O tipo de perfil informado não existe.', $requestData)->error;
            } else {

                if ($resource->id) {
                    $pessoa = $resource->pessoa;

                    // Verifica se o perfil já existe para outra pessoa (duplicidade de cadastro)
                    $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->modelPessoaPerfil::class, [
                        'pessoa_id' => $pessoa->id,
                        'perfil_tipo_id' => $perfil->perfil_tipo_id
                    ], $perfil->id ?? null, false);

                    if ($validacaoRecursoExistente->count()) {
                        $perfilTipo = PessoaPerfilTipo::withTrashed()->find($perfil->perfil_tipo_id);
                        $arrayErrors->{"perfil_{$perfil->perfil_tipo_id}"} = LogHelper::gerarLogDinamico(404, "O perfil {$perfilTipo->nome} já existe para esta pessoa.", $requestData)->error;
                    }
                }

                $newPerfil = new $this->modelPessoaPerfil;
                $newPerfil->fill($perfil->toArray());
                array_push($perfis, $newPerfil);
            }
        }

        $retorno = new Fluent();
        $retorno->perfis = $perfis;
        $retorno->arrayErrors = $arrayErrors;

        return $retorno;
    }
}
