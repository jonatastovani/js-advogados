<?php

namespace App\Services\Pessoa;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\PessoaTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Pessoa\Pessoa;
use App\Models\Referencias\PessoaStatusTipo;
use App\Models\Referencias\PessoaSubtipo;
use App\Services\Service;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class PessoaService extends Service
{
    use ConsultaSelect2ServiceTrait;

    public function __construct(
        Pessoa $model,
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

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'A Pessoa não foi encontrada.',
        ], $options));
    }

    /**
     * Carrega os relacionamentos completos para o serviço, aplicando manipulações dinâmicas
     * com base nas opções fornecidas. Este método ajusta os relacionamentos a serem carregados
     * dependendo do tipo de pessoa (Física ou Jurídica) e considera se a chamada é externa 
     * para evitar carregamentos duplicados ou redundantes.
     *
     * @param array $options Opções para manipulação de relacionamentos:
     *     - 'caseTipoPessoa' (PessoaTipoEnum|null): Define o tipo de pessoa para o carregamento
     *       específico. Pode ser Pessoa Física ou Jurídica. Se não for informado, aplica um 
     *       comportamento padrão.
     *     - 'withOutClass' (array|string|null): Classes que devem ser excluídas do carregamento
     *       de relacionamentos, útil para evitar referências circulares.
     *
     * @return array Retorna um array de relacionamentos manipulados.
     *
     * @throws Exception Se houver algum erro durante o carregamento dinâmico dos serviços.
     *
     * Lógica:
     * - Verifica o tipo de pessoa (Física ou Jurídica) e ajusta os relacionamentos com base
     *   no serviço correspondente (PessoaFisicaService ou PessoaJuridicaService).
     * - Se nenhum tipo de pessoa for especificado, adiciona o relacionamento genérico 'pessoa_dados'.
     * - Utiliza a função `mergeRelationships` para mesclar relacionamentos existentes com 
     *   os novos, aplicando prefixos onde necessário.
     *
     * Exemplo de Uso:
     * ```php
     * $service = new PessoaService();
     * $relationships = $service->loadFull([
     *     'caseTipoPessoa' => PessoaTipoEnum::PESSOA_FISICA,
     * ]);
     * ```
     */
    public function loadFull($options = []): array
    {
        // Lista de classes a serem excluídas para evitar referência circular
        $withOutClass = array_merge(
            (array)($options['withOutClass'] ?? []), // Mescla com os existentes em $options
            [self::class] // Adiciona a classe atual
        );
        // Tipo de pessoa enviado para o carregamento específico do tipo de pessoa
        $caseTipoPessoa = $options['caseTipoPessoa'] ?? null;

        // Função para carregar dados de Pessoa Física ou Jurídica dinamicamente
        $carregarPessoaPorTipo = function ($serviceTipoPessoa, $relationships) use ($options, $withOutClass) {
            $relationships = $this->mergeRelationships(
                $relationships,
                app($serviceTipoPessoa)->loadFull(array_merge(
                    $options, // Passa os mesmos $options
                    [
                        'withOutClass' => $withOutClass, // Garante que o novo `withOutClass` seja propagado
                    ]
                )),
                [
                    'addPrefix' => 'pessoa_dados.' // Adiciona um prefixo aos relacionamentos externos
                ]
            );

            return $relationships;
        };

        $relationships = ['enderecos'];

        // Verifica se PessoaPerfilService está na lista de exclusão
        $classImport = PessoaPerfilService::class;
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
                    'addPrefix' => 'pessoa_perfil.'
                ]
            );
        }

        // Verifica o tipo de pessoa e ajusta os relacionamentos
        if ($caseTipoPessoa === PessoaTipoEnum::PESSOA_FISICA->value) {
            $relationships = $carregarPessoaPorTipo(PessoaFisicaService::class, $relationships);
        } elseif ($caseTipoPessoa === PessoaTipoEnum::PESSOA_JURIDICA->value) {
            $relationships = $carregarPessoaPorTipo(PessoaJuridicaService::class, $relationships);
        } else {
            $relationships = array_merge(
                $relationships,
                [
                    'pessoa_dados',
                ]
            );
        }

        // Verifica se PessoaPerfilService está na lista de exclusão
        $classImport = PessoaDocumentoService::class;
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
                    'addPrefix' => 'documentos.'
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
