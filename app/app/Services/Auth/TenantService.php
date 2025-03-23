<?php

namespace App\Services\Auth;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Auth\Domain;
use App\Models\Auth\Tenant;
use App\Services\Service;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

class TenantService extends Service
{
    use ConsultaSelect2ServiceTrait;

    public function __construct(Tenant $model)
    {
        parent::__construct($model);
    }

    /**
     * Traduz os campos com base no array de dados fornecido.
     *
     * @param array $dados O array de dados contendo as informações de como traduzir os campos.
     * - 'campos_busca' (array de campos que devem ser traduzidos). Os campos que podem ser enviados dentro do array são:
     * - ex: 'campos_busca' => ['col_nome'] (mapeado para '[tableAsName].nome')
     * - 'campos_busca_todos' (se definido, todos os campos serão traduzidos)
     * @return array Os campos traduzidos com base nos dados fornecidos.
     */
    public function traducaoCampos(array $dados)
    {
        // $aliasCampos = $dados['aliasCampos'] ?? [];
        // $modelAsName = $this->model->getTableAsName();
        // $arrayAliasCampos = [
        //     'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : $modelAsName,
        //     'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $modelAsName,
        // ];

        // $arrayCampos = [
        //     'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
        //     'col_descricao' => ['campo' => $arrayAliasCampos['col_nome'] . '.descricao'],
        // ];
        // return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    public function updateCliente(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoUpdateCliente($requestData, $requestData->uuid);

        try {
            return DB::transaction(function () use ($resource) {

                $domains = $resource->domains;
                unset($resource->domains);

                $resource->save();
        
                foreach ($domains as $domain) {
                    $domain->save();
                }

                // $this->executarEventoWebsocket();
                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    protected function verificacaoEPreenchimentoRecursoUpdateCliente(Fluent $requestData, $id = null): Model
    {
        $arrayErrors = new Fluent();
        $resource = $this->model::find(tenant('id'));

        $domains = [];
        foreach ($requestData['domains'] as $value) {

            $validacaoDomainId = ValidationRecordsHelper::validateRecord(Domain::class, ['id' => $value['id']]);
            if (!$validacaoDomainId->count()) {
                $arrayErrors->{"domain_" . $value['id']} = LogHelper::gerarLogDinamico(404, 'O Domínio informado não existe ou foi excluída.', $requestData)->error;
            }

            $domains[] = Domain::find($value['id'])->fill($value);
        }

        // Preenche os atributos da model com os dados do request e conforme os campos $fillable definido na model
        $resource->fill($requestData->toArray());
        $resource->domains = $domains;

        // RestResponse::createTestResponse([$resource->toArray(), $requestData->toArray()]);

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'O tenant não foi encontrado.',
        ]);
    }

    public function loadFull($options = []): array
    {
        return [
            'domains'
        ];
    }
    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
