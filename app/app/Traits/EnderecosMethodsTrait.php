<?php

namespace App\Traits;

use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;

trait EnderecosMethodsTrait
{

    public function atualizarEnderecosEnviados($resource, $enderecosExistentes, $enderecosEnviados, $options = [])
    {
        // IDs dos enderecos já salvos
        $existingEnderecos = collect($enderecosExistentes)->pluck('id')->toArray();
        // IDs enviados (exclui novos enderecos sem ID)
        $submittedEnderecosIds = collect($enderecosEnviados)->pluck('id')->filter()->toArray();

        // Enderecos ausentes no PUT devem ser excluídos
        $idsToDelete = array_diff($existingEnderecos, $submittedEnderecosIds);
        if ($idsToDelete) {
            foreach ($idsToDelete as $id) {
                $enderecoDelete = $this->modelEndereco::find($id);
                if ($enderecoDelete) {
                    $enderecoDelete->delete();
                }
            }
        }

        foreach ($enderecosEnviados as $endereco) {

            if ($endereco->id) {
                $enderecoUpdate = $this->modelEndereco::find($endereco->id);
                $enderecoUpdate->fill($endereco->toArray());
            } else {
                $enderecoUpdate = $endereco;
                $enderecoUpdate->parent_id = $resource->pessoa->id;
                $enderecoUpdate->parent_type = $this->modelPessoa->getMorphClass();
            }

            $enderecoUpdate->save();
        }
    }

    protected function verificacaoEnderecos(Fluent $requestData, Model $resource, Fluent $arrayErrors): Fluent
    {
        $enderecosData = $requestData->enderecos;
        $enderecos = [];
        foreach ($enderecosData as $endereco) {
            $endereco = new Fluent($endereco);

            // Verifica se o endereco já existe para essa pessoa (duplicidade de cadastro)
            $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->modelEndereco::class, [
                'numero' => $endereco->numero,
                'logradouro' => $endereco->logradouro,
                'cidade' => $endereco->cidade
            ], $endereco->id ?? null);

            $logradouroSemEspaco = str_replace(' ', '_', $endereco->logradouro);
            $numeroSemEspaco = str_replace(' ', '_', $endereco->numero);
            if ($validacaoRecursoExistente->count()) {
                $arrayErrors->{"endereco_{$logradouroSemEspaco}_{$numeroSemEspaco}"} = LogHelper::gerarLogDinamico(404, "O endereco {$endereco->logradouro}, {$endereco->numero} já existe cadastrado para essa pessoa.", $requestData)->error;
            }

            $newEndereco = new $this->modelEndereco;
            $newEndereco->fill($endereco->toArray());
            array_push($enderecos, $newEndereco);
        }

        $retorno = new Fluent();
        $retorno->enderecos = $enderecos;
        $retorno->arrayErrors = $arrayErrors;

        return $retorno;
    }
}
