<?php

namespace App\Services\Pessoa;

use App\Common\RestResponse;
use App\Enums\PessoaPerfilTipoEnum;
use App\Enums\PessoaTipoEnum;
use App\Models\Pessoa\Pessoa;
use App\Models\Pessoa\PessoaPerfil;
use App\Services\Service;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class PessoaPerfilService extends Service
{
    use ConsultaSelect2ServiceTrait;

    public function __construct(
        PessoaPerfil $model,
        public PessoaFisicaService $pessoaFisicaService
    ) {
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
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $modelAsName = $this->model->getTableAsName();
        $arrayAliasCampos = [
            'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : $modelAsName,
            'col_mae' => isset($aliasCampos['col_mae']) ? $aliasCampos['col_mae'] : $modelAsName,
            'col_pai' => isset($aliasCampos['col_pai']) ? $aliasCampos['col_pai'] : $modelAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_mae' => ['campo' => $arrayAliasCampos['col_mae'] . '.mae'],
            'col_pai' => ['campo' => $arrayAliasCampos['col_pai'] . '.pai'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    public function show(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        $resource->load('pessoa');
        // Para carregar o relacionamento de pessoa_dados completo conforme o tipo de pessoa
        $resource->load($this->loadFull(['caseTipoPessoa' => $resource->pessoa->pessoa_dados]));
        $resource->pessoa->load('pessoa_perfil.perfil_tipo');
        if ($resource->perfil_tipo_id == PessoaPerfilTipoEnum::USUARIO->value) {
            $resource->load('user.user_tenant_domains.domain');
        }
        return $resource->toArray();
    }

    public function showEmpresa(Fluent $requestData = null)
    {
        // Se não encontrar o perfil, retorna vazio
        $resource = PessoaPerfil::where('perfil_tipo_id', PessoaPerfilTipoEnum::EMPRESA->value)->orderBy('created_at', 'asc')->first();
        return $resource ? $resource->toArray() : [];
    }

    public function destroy(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        $resource->load([
            'cliente_servicos_vinculados',
            'participante_servicos_vinculados',
            'participante_servicos_vinculados',
            'user.user_tenant_domains',
        ]);

        $fluentErrors = new Fluent();

        switch ($resource->perfil_tipo_id) {
            case PessoaPerfilTipoEnum::CLIENTE->value:

                $totalServicosVinculadosCliente = count($resource->cliente_servicos_vinculados ?? []);
                if ($totalServicosVinculadosCliente) {
                    $fluentErrors->cliente_servicos_vinculados = "Serviços vinculados ao perfil Cliente: {$totalServicosVinculadosCliente}.";
                }
                break;

            case PessoaPerfilTipoEnum::PARCEIRO->value:

                $totalServicosVinculadosParceiro = count($resource->participante_servicos_vinculados ?? []);
                if ($totalServicosVinculadosParceiro) {
                    $fluentErrors->participante_parceiro_vinculado  = "Serviços vinculados ao perfil Parceiro: {$totalServicosVinculadosParceiro}.";
                }
                break;

            case PessoaPerfilTipoEnum::TERCEIRO->value:

                $totalServicosVinculadosTerceiro = count($resource->participante_servicos_vinculados ?? []);
                if ($totalServicosVinculadosTerceiro) {
                    $fluentErrors->participante_terceiro_vinculado  = "Serviços vinculados ao perfil Terceiro: {$totalServicosVinculadosTerceiro}.";
                }
                break;

            case PessoaPerfilTipoEnum::USUARIO->value:

                $usuarioVinculado = $resource->user->user_tenant_domains ?? [];

                if (count($usuarioVinculado)) {
                    // Verifica se todos os domínios podem ser excluídos forçadamente
                    foreach ($usuarioVinculado as $userTenantDomain) {
                        if (! $this->deleteForceTest($userTenantDomain)) {
                            $fluentErrors->usuario = "Este usuário possui vínculos e não pode ser excluído. Utilize a funcionalidade de desativação.";
                            break; // Interrompe ao encontrar o primeiro impedimento
                        }
                    }
                }

                break;

            case PessoaPerfilTipoEnum::EMPRESA->value:
                $fluentErrors->usuario = "Perfis de Empresa não podem ser excluídos. Utilize a funcionalidade de desativação.";
                break;
        }

        if (count($fluentErrors->toArray())) {
            RestResponse::createGenericResponse(
                ['errors' => $fluentErrors->toArray()],
                422,
                "Este perfil possui vínculos e não pode ser excluído."
            )->throwResponse();
        }

        try {
            return DB::transaction(function () use ($resource) {

                $resource->delete();

                // $this->executarEventoWebsocket();
                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'O Perfil da Pessoa não foi encontrado.',
        ], $options));
    }

    public function loadFull($options = []): array
    {
        // Lista de classes a serem excluídas para evitar referência circular
        $withOutClass = array_merge(
            (array)($options['withOutClass'] ?? []), // Mescla com os existentes em $options
            [self::class] // Adiciona a classe atual
        );

        $relationships = [
            'perfil_tipo',
        ];

        // Verifica se PessoaService está na lista de exclusão
        $classImport = PessoaService::class;
        if (!in_array($classImport, $withOutClass)) {
            $relationships = $this->mergeRelationships(
                $relationships,
                app($classImport)->loadFull(array_merge(
                    $options, // Passa os mesmos $options
                    [
                        'withOutClass' => $withOutClass, // Garante que o novo `withOutClass` seja propagado
                    ]
                )),
                [
                    'addPrefix' => 'pessoa.'
                ]
            );
        }

        return $relationships;
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
