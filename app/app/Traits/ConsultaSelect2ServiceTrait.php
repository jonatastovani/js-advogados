<?php

namespace App\Traits;

use App\Helpers\StringHelper;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Fluent;

trait ConsultaSelect2ServiceTrait
{
    /**
     * Executa a consulta para retorno de dados no formato Select2.
     *
     * @param Fluent $requestData A requisição HTTP
     * @param Fluent|null $dados Os dados fornecidos, incluindo campos de filtros e o campo de texto
     * @return Fluent|array|Builder[] O array de resultados formatado ou a query se solicitado
     */
    public function executaConsultaSelect2(Fluent $requestData, Fluent $dados = null)
    {
        // Constrói a query base
        $query = $this->buildBaseQuery();

        // Aplica os filtros
        $query = $this->applyFilters($query, $requestData, $dados);

        // Verifica se o usuário quer retornar apenas a query
        if (isset($dados->retornarQuery) && $dados->retornarQuery === true) {
            return $query; // Retorna a query para ser manipulada externamente
        }

        // Definindo o campo de texto dinamicamente ou usando um padrão
        $campoTexto = $dados->campoTexto ?? 'nome'; // O padrão é 'nome'

        // Definir o limite de resultados (25 como padrão)
        $limite = $dados->limite ?? 25;

        // Executa a consulta com o limite e formata os resultados
        return $this->formatSelect2Results($query->limit($limite)->get(), $campoTexto);
    }

    /**
     * Constrói a query base na model associada.
     *
     * @return Builder A query base
     */
    protected function buildBaseQuery(): Builder
    {
        return $this->model->newQuery();
    }

    // /**
    //  * Aplica os filtros baseados no texto e campos de busca fornecidos.
    //  *
    //  * @param Builder $query A query na qual os filtros serão aplicados
    //  * @param Fluent $requestData A requisição HTTP
    //  * @param Fluent|null $dados Os dados fornecidos com os campos de filtros
    //  * @return Builder A query com os filtros aplicados
    //  */
    // protected function applyFilters(Builder $query, Fluent $requestData, Fluent $dados = null): Builder
    // {
    //     if (isset($dados->camposFiltros) && is_array($dados->camposFiltros)) {
    //         $arrFields = $dados->camposFiltros;

    //         if ($requestData->text && !empty($requestData->text)) {
    //             $textoBusca = $requestData->text;
    //             // $textoBusca = StringHelper::removeAccents($textoBusca);

    //             // Adiciona os filtros de texto na query usando ILIKE
    //             $query->where(function (Builder $query) use ($arrFields, $textoBusca) {
    //                 foreach ($arrFields as $field) {
    //                     // $query->orWhereRaw("{$field} ILIKE ?", ["%{$textoBusca}%"]);
    //                     //     $query->orWhereRaw("
    //                     //     REGEXP_REPLACE(LOWER($field), '[^a-z0-9]', '', 'g') ILIKE REGEXP_REPLACE(?, '[^a-z0-9]', '', 'g')
    //                     // ", ["%{$textoBusca}%"]);
    //                     $query->orWhereRaw("
    //                         TRANSLATE(LOWER({$field}), 'áàãâäéèêëíìîïóòõôöúùûüçñ', 'aaaaaeeeeiiiiooooouuuucn') 
    //                         LIKE TRANSLATE(LOWER(?), 'áàãâäéèêëíìîïóòõôöúùûüçñ', 'aaaaaeeeeiiiiooooouuuucn')
    //                     ", ["%{$textoBusca}%"]);
    //                 }
    //             });
    //         }
    //     }

    //     return $query;
    // }

    /**
     * Aplica filtros com base no texto fornecido pelo usuário,
     * buscando nos campos configurados, com suporte a remoção de acentos.
     *
     * @param Builder $query Query base que será modificada.
     * @param Fluent $requestData Dados da requisição (deve conter 'text').
     * @param Fluent|null $dados Dados adicionais que contêm os campos de filtros.
     * @return Builder Query com os filtros aplicados.
     */
    protected function applyFilters(Builder $query, Fluent $requestData, Fluent $dados = null): Builder
    {
        if (isset($dados->camposFiltros) && is_array($dados->camposFiltros)) {
            $arrFields = $dados->camposFiltros;

            if (!empty($requestData->text)) {
                $textoBusca = mb_strtolower(StringHelper::removeAccents($requestData->text));
                [$originais, $semAcento] = StringHelper::getTranslatePostgresAcentos();

                $query->where(function (Builder $query) use ($arrFields, $textoBusca, $originais, $semAcento) {
                    foreach ($arrFields as $field) {
                        $query->orWhereRaw("
                        TRANSLATE(LOWER({$field}), '{$originais}', '{$semAcento}')
                        LIKE ?
                    ", ["%{$textoBusca}%"]);
                    }
                });
            }
        }

        return $query;
    }

    /**
     * Formata os resultados da consulta no formato esperado pelo Select2.
     *
     * @param \Illuminate\Database\Eloquent\Collection $results Os resultados da consulta
     * @param string $campoTexto O campo que será utilizado como o 'text' no resultado
     * @return Fluent O array de resultados formatado para Select2, encapsulado em um Fluent
     */
    protected function formatSelect2Results($results, string $campoTexto = 'nome'): Fluent
    {
        $data = [];

        foreach ($results as $item) {
            // Aqui usamos o campo 'campoTexto' que foi passado dinamicamente
            $data[] = [
                'id' => $item->id,                 // Adapte conforme o campo de ID da sua model
                'text' => $item->{$campoTexto},    // Usa o campo passado dinamicamente ou o padrão 'nome'
            ];
        }

        // Se nenhum resultado for encontrado, retornar uma mensagem padrão
        if (empty($data)) {
            $data[] = [
                'id' => 0,
                'text' => 'Nenhum resultado encontrado',
            ];
        }

        // Retorna os dados formatados encapsulados em um Fluent
        return new Fluent($data);
    }
}
