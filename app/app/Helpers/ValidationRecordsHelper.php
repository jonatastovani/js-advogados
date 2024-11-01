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
     * @param bool $checkSoftDelete Define se o soft delete será verificado (padrão: true).
     * @return Model|null Retorna o array do registro encontrado ou null caso não encontre.
     */
    public static function validateRecord($modelClass, array $conditions, bool $checkSoftDelete = true)
    {
        $query = $modelClass::query();

        // Aplica as condições where
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }

        //Verifica se a trait SoftDeletes está sendo utilizada no modelo
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass))) {
            if (!$checkSoftDelete) {
                $query->withTrashed();
            }
        }

        // RestResponse::createTestResponse([$query->toSql(), $query->getBindings(), $query->get()]);
        return $query->get();
    }

    /**
     * Valida se o recurso já existe com os campos enviados na requisição.
     *
     * @param Model $modelClass O modelo Eloquent a ser verificado.
     * @param array $fields Os campos que devem ser validados.
     * @param int|null $id O ID do registro atual, se for uma atualização (para exclusão do próprio registro).
     * @return Model|null Retorna os registros encontrados.
     */
    public static function validarRecursoExistente($modelClass, array $fields, $id = null)
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

        return $query->get();
    }
}
