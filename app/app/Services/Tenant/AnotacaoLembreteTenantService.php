<?php

namespace App\Services\Tenant;

use App\Enums\AnotacaoLembreteTenantTipoEnum;
use App\Models\Servico\Servico;
use App\Models\Tenant\AnotacaoLembreteTenant;
use App\Services\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class AnotacaoLembreteTenantService extends Service
{
    public function __construct(
        public AnotacaoLembreteTenant $model,
        public Servico $modelServico,
    ) {}

    /**
     * Traduz os campos com base no array de dados fornecido.
     *
     * @param array $dados O array de dados contendo as informações de como traduzir os campos.
     * - 'campos_busca' (array de campos que devem ser traduzidos). Os campos que podem ser enviados dentro do array são:
     * - ex: 'campos_busca' => ['col_titulo'] (mapeado para '[tableAsName].titulo')
     * - 'campos_busca_todos' (se definido, todos os campos serão traduzidos)
     * @return array Os campos traduzidos com base nos dados fornecidos.
     */
    public function traducaoCampos(array $dados)
    {
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $permissionAsName = $this->model->getTableAsName();
        $arrayAliasCampos = [
            'col_titulo' => isset($aliasCampos['col_titulo']) ? $aliasCampos['col_titulo'] : $permissionAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $permissionAsName,
        ];

        $arrayCampos = [
            'col_titulo' => ['campo' => $arrayAliasCampos['col_titulo'] . '.titulo'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_titulo'], $dados);
    }

    public function storeAnotacaoServico(Fluent $requestData)
    {
        return $this->storePadraoAnotacao($requestData, $requestData->servico_uuid, $this->modelServico);
    }

    public function storePadraoAnotacao(Fluent $requestData, string $idParent, Model $modelParent)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData);

        // Inicia a transação
        DB::beginTransaction();

        try {
            $resource->tipo = AnotacaoLembreteTenantTipoEnum::ANOTACAO->value;
            $resource->parent_id = $idParent;
            $resource->parent_type = $modelParent->getMorphClass();
            $resource->save();
            DB::commit();
            // $this->executarEventoWebsocket();
            return $resource->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function updateAnotacaoServico(Fluent $requestData)
    {
        return $this->updatePadrao($requestData, $requestData->servico_uuid, $this->modelServico);
    }

    public function updatePadrao(Fluent $requestData, string $idParent, Model $modelParent)
    {
        $resource = parent::buscarRecurso($requestData, [
            'message' => 'A Anotação/Lembrete não foi encontrado.',
            'conditions' => [
                'id' => $requestData->uuid,
                'parent_id' => $idParent,
            ]
        ]);

        $resource->fill($requestData->toArray());

        // Inicia a transação
        DB::beginTransaction();

        try {
            $resource->save();
            DB::commit();
            // $this->executarEventoWebsocket();
            return $resource->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $resource = null;
        $resource = $id ? $this->buscarRecurso($requestData) : new $this->model;
        $resource->fill($requestData->toArray());
        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'A Anotação/Lembrete não foi encontrado.'
        ]);
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
