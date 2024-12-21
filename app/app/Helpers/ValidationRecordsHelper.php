<?php

namespace App\Helpers;

use App\Common\RestResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ValidationRecordsHelper
{
    /**
     * Verifica se o registro com base nas condições enviadas existe e não está deletado (se aplicável).
     *
     * @param Model $modelClass O modelo Eloquent a ser verificado.
     * @param array $conditions Condições de where que serão aplicadas na consulta.
     * @param bool $excludeSoftDeleted Define se os registros soft-deleted devem ser excluídos da consulta (padrão: true).
     * @return Collection Retorna a coleção de registros encontrados ou uma coleção vazia caso não encontre.
     */
    public static function validateRecord($modelClass, array $conditions, bool $excludeSoftDeleted = true)
    {
        $query = $modelClass::query();

        // Aplica as condições where
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }

        // Verifica se a trait SoftDeletes está sendo utilizada no modelo
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass))) {
            if (!$excludeSoftDeleted) {
                $query->withTrashed();
            }
        }

        return $query->get();
    }


    /**
     * Valida se o recurso já existe com os campos enviados na requisição.
     *
     * @param Model $modelClass O modelo Eloquent a ser verificado.
     * @param array $fields Os campos que devem ser validados.
     * @param int|null $id O ID do registro atual, se for uma atualização (para exclusão do próprio registro).
     * @param bool $excludeSoftDeleted Define se os registros soft-deleted devem ser excluídos da consulta (padrão: true).
     * @return Model|null Retorna os registros encontrados.
     */
    public static function validarRecursoExistente($modelClass, array $fields, $id = null, bool $excludeSoftDeleted = true)
    {
        $query = $modelClass::query();

        // Adiciona as condições de where com base nos campos enviados
        foreach ($fields as $field => $value) {
            $query->where($field, $value);
        }

        // Exclui o próprio registro, se estiver atualizando
        if ($id !== null) {
            $query->where('id', '<>', $id);
        }

        // Verifica se a trait SoftDeletes está sendo utilizada no modelo
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass))) {
            if (!$excludeSoftDeleted) {
                $query->withTrashed();
            }
        }

        return $query->get();
    }
}
