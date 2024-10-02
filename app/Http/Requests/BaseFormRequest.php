<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Sobrescreve o comportamento padrão de falha de validação para retornar
     * uma resposta JSON personalizada e gerar logs de validação, se ativado.
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        $code = 422;
        $mensagem = "Falha na validação da requisição.";

        // Gerar log apenas se estiver habilitado
        if ($this->shouldLogValidation()) {
            $traceId = CommonsFunctions::generateLog("$code | $mensagem | Request: " . json_encode($this->all()) . " | Erros: " . json_encode($validator->errors()), ['channel' => 'validacao', 'type' => 'error']);
        }

        $response = RestResponse::createGenericResponse(["errors" => $validator->errors()], $code, $mensagem, $traceId ?? null);

        // // Padrão de retorno de erro de validação
        // $response = response()->json([
        //     'status' => 'error',
        //     'message' => 'Erro na validação dos dados.',
        //     'errors' => $validator->errors(),
        //     'trace_id' => $traceId ?? null, // Adiciona o Trace ID no retorno se existir
        // ], 422);

        throw new HttpResponseException($response->autoResponse());
    }

    /**
     * Define as regras de validação padrão. Cada FormRequest filho deve sobrescrever isso.
     */
    abstract public function rules();

    /**
     * Define as mensagens de validação customizadas, podendo sobrescrever as padrões.
     */
    public function messages()
    {
        $messages = [
            'required' => 'O campo :attribute é obrigatório.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'numeric' => 'O campo :attribute deve ser um número.',
            'boolean' => 'O campo :attribute deve ser booleano.',
            'array' => 'O campo :attribute deve ser array.',
            'string' => 'O campo :attribute deve ser um texto de até 256 caracteres.',
            'text' => 'O campo :attribute deve ser um texto.',
            'date' => 'O campo :attribute deve ser uma data.',
            'max' => 'O campo :attribute deve ter no máximo :max caracteres.',
            'min' => 'O campo :attribute deve ter no mínimo :min caracteres.',
            'date_format' => 'O campo :attribute deve possuir o formato :format.',
            'required_with' => 'O campo :attribute deve ser informado.',
            'required_if' => 'O campo :attribute é obrigatório quando o campo :other é :value.',
            'between' => 'O campo :attribute deve estar entre :min e :max.',
            'in' => 'O campo :attribute deve ser um dos seguintes valores: :values.',
            'uuid' => 'O campo :attribute deve ser um UUID válido.',

            'nome.regex' => 'O campo :attribute não deve conter números.',
            'nome_social.regex' => 'O campo :attribute não deve conter números.',
            'mae.regex' => 'O campo :attribute não deve conter números.',
            'pai.regex' => 'O campo :attribute não deve conter números.',
            'matricula.regex' => 'O campo :attribute deve conter somente números.',

            // 'presos.*.nome.regex' => 'O campo :attribute não deve conter números.',
            // 'presos.*.nome_social.regex' => 'O campo :attribute não deve conter números.',
            // 'presos.*.mae.regex' => 'O campo :attribute não deve conter números.',
            // 'presos.*.pai.regex' => 'O campo :attribute não deve conter números.',
            // 'presos.*.matricula.regex' => 'O campo :attribute deve conter somente números.',
        ];

        return array_merge($messages, $this->customMessages());
    }

    /**
     * Retorna o nome customizado dos atributos para uso nas mensagens de validação.
     */
    public function attributes()
    {
        $attributes = [];
        return array_merge($attributes, $this->customAttributeNames());
    }

    /**
     * Regras específicas de mensagens customizadas que podem ser sobrescritas nos FormRequests filhos.
     * @return array
     */
    protected function customMessages(): array
    {
        return [];
    }

    /**
     * Nomes de atributos customizados que podem ser sobrescritos nos FormRequests filhos.
     * @return array
     */
    protected function customAttributeNames(): array
    {
        return [];
    }

    /**
     * Indica se a geração de logs para validação está habilitada.
     * Você pode desativar essa funcionalidade em Form Requests filhos, sobrescrevendo este método.
     * 
     * @return bool
     */
    protected function shouldLogValidation(): bool
    {
        return true; // Define como true por padrão, mas pode ser desabilitado por FormRequests específicos
    }

    public function rulesShowWithTrashed(): array
    {
        return [
            // Para casos de busca de registros que tenham sido excluídos
            'withTrashed' => 'nullable|boolean',
        ];
    }
}
