<?php

namespace App\Traits;

use ReflectionClass;
use Illuminate\Support\Fluent;
use App\Helpers\StringHelper;

trait EnumRenderTrait
{
    /**
     * Renderiza o Enum com base no tipo enviado.
     *
     * @param string $type O tipo de enum que será renderizado ('permission', 'permission_group', etc.)
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function renderPhpEnum(string $type, $request): array
    {
        // Busca o resource com base no tipo
        $resource = $this->buscarRecurso($request);

        // Gera o código PHP formatado para ser exibido no modal
        $formattedArray = $this->formatPhpEnumArray($resource->toArray());

        // Obtém a classe e o caminho do Enum com base no tipo
        $enumClass = $this->getEnumClassByType($type);

        // Gera o Enum
        $code = $this->generateEnumCode([
            'formattedArray' => $formattedArray,
            'id' => $resource->id,
            'nome' => $resource->nome
        ]);

        // Usa Reflection para obter o caminho do arquivo onde o Enum está localizado
        $reflection = new ReflectionClass($enumClass);
        $path = $reflection->getFileName();

        // Monta os dados que serão retornados
        $fluent = new Fluent();
        $fluent->code = $code;
        $fluent->original = $resource->toArray();
        $fluent->class = $enumClass;
        $fluent->path = $path; // Caminho real do arquivo Enum
        return $fluent->toArray();
    }

    /**
     * Mapeia o tipo de enum para a classe correspondente.
     *
     * @param string $type O tipo de enum ('permission', 'permission_group', etc.)
     * @return string O nome da classe correspondente ao tipo
     */
    private function getEnumClassByType(string $type): string
    {
        $enumMap = [
            'permission' => \App\Helpers\EnumPermissionsHelper::class,
            'permission_group' => \App\Helpers\EnumPermissionGroupsHelper::class,
            'permission_module' => \App\Helpers\EnumPermissionModulesHelper::class,
        ];

        // Retorna a classe correspondente ao tipo ou uma exceção caso o tipo não exista
        if (!array_key_exists($type, $enumMap)) {
            throw new \InvalidArgumentException("Tipo de enum '{$type}' não encontrado.");
        }

        return $enumMap[$type];
    }

    /**
     * Formata o array PHP a partir dos campos do recurso.
     *
     * @param array $fields
     * @return string
     */
    private function formatPhpEnumArray(array $fields): string
    {
        $unsetColumnsLogActivity = [
            'id',
            'created_user_id',
            'created_ip',
            'created_at',
            'updated_user_id',
            'updated_ip',
            'updated_at',
            'deleted_user_id',
            'deleted_ip',
            'deleted_at',
        ];

        // Monta um código PHP formatado como array associativo
        $formatted = "";
        foreach ($fields as $key => $value) {
            if (!in_array($key, $unsetColumnsLogActivity)) {
                // Verifica se o valor é uma string e coloca aspas simples, caso contrário, deixa o valor como está
                $formattedValue = is_string($value) ? "'{$value}'" : $value;
                $formatted .= "\n            '{$key}' => {$formattedValue},";
            }
        }

        return $formatted;
    }

    /**
     * Gera o código do Enum com base nos campos fornecidos.
     *
     * @param array $fields
     * @return string
     */
    private function generateEnumCode(array $fields): string
    {
        $nomeConstante = StringHelper::formatAsConstant($fields['nome']); // Converte o nome para um formato de constante
        $formattedArray = $fields['formattedArray'];
        $id = $fields['id'];

        // Gera o código do case e o bloco do match
        $enumCode =
        <<<PHP

        case {$nomeConstante} = {$id};

        public function detalhes(): array
        {
            return match (\$this) {
                self::{$nomeConstante} => [
                    'id' => self::{$nomeConstante}, $formattedArray
                ],
            };
        }
        PHP;

        return $enumCode;
    }
}
