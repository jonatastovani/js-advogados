<?php

namespace App\Common;

use App\common\RestResponse;
use App\Helpers\UUIDsHelpers;
use App\Models\Auth\UserTenantDomain;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommonsFunctions
{

    /**
     * Gera um ID para o Log
     */
    static function generateTraceId()
    {
        return uniqid('GPU_API|');
    }

    /**
     * Gera um log estruturado com informações detalhadas sobre o contexto da execução.
     *
     * Este método registra um log em um canal específico definido por meio das opções
     * e inclui informações como o arquivo e a linha onde o log foi gerado, o ID do usuário,
     * o endereço IP, a URL e o método HTTP da requisição, além de um trace ID exclusivo para
     * rastreamento. O log pode ser gravado em diferentes canais e níveis, como 'info' ou 'error'.
     *
     * @param string $mensagem A mensagem principal que será registrada no log.
     * @param array $options Um array de opções personalizáveis para o log:
     *                       - 'channel' (string): O canal de log a ser utilizado (ex: 'acesso_recusado', 'usuario_sem_permissao').
     *                       - 'type' (string): O tipo de log, como 'info' ou 'error'. O padrão é 'error'.
     *
     * @return string Retorna o trace ID gerado para o log, que pode ser utilizado para rastreamento em sistemas externos.
     *
     * Exemplo de Uso:
     * ```php
     * $traceId = CommonsFunctions::generateLog(
     *     "Usuário não autorizado a acessar este recurso.",
     *     ['channel' => 'usuario_sem_permissao', 'type' => 'error']
     * );
     * ```
     */
    static function generateLog($mensagem, array $options = []): string
    {
        // Definir o canal de log a partir das opções ou do arquivo config/logging.php
        $channel = $options['channel'] ?? 'default';
        $type = $options['type'] ?? 'error';

        // Mapear os canais de log específicos
        $channelMap = [
            'acesso_recusado' => 'gpu_app_acesso_recusado_file',
            'usuario_sem_permissao' => 'gpu_app_usuario_sem_permissao_file',
            'rota_nao_encontrada' => 'gpu_app_rota_nao_encontrada_file',
        ];

        // Definir o canal baseado no map ou usar o padrão do config
        $channel = $channelMap[$channel] ?? config('logging.default_gpu_app');

        // Capturar informações do backtrace para identificar a origem
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        $chamador = end($trace);
        $diretorio = $chamador['file'] ?? 'Desconhecido';
        $linha = $chamador['line'] ?? 'Desconhecida';

        // Gerar um traceId para rastrear o log
        $traceId = CommonsFunctions::generateTraceId();

        // Capturar informações da requisição
        $url = request()->fullUrl();
        $metodoHttp = request()->method();
        $userId = auth()->check() ? auth()->id() : 'Guest'; // Garantir que o usuário esteja autenticado
        $userIp = UserInfo::get_ip();

        // Montar a mensagem de log com estrutura clara
        $mensagemLog = [
            "Mensagem" => $mensagem,
            "Arquivo" => $diretorio,
            "Linha" => $linha,
            "UserId" => $userId,
            "UserIp" => $userIp,
            "Trace ID" => $traceId,
            "URL" => $url,
            "Método HTTP" => $metodoHttp,
        ];

        // Transformar o array de log em uma string formatada
        $mensagemFormatada = collect($mensagemLog)
            ->map(function ($value, $key) {
                return "$key: $value";
            })
            ->implode(' | ');

        // Registrar o log no canal correto com base no tipo
        switch ($type) {
            case 'info':
                Log::channel($channel)->info($mensagemFormatada);
                break;

            default:
                Log::channel($channel)->error($mensagemFormatada);
                break;
        }

        return $traceId;
    }

    /**
     * Retorna um array de mensagens para uso do Validator
     */
    static function getMessagesValidate(): array
    {
        return [
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

            'nome.regex' => 'O campo :attribute não deve conter números.',
            'nome_social.regex' => 'O campo :attribute não deve conter números.',
            'mae.regex' => 'O campo :attribute não deve conter números.',
            'pai.regex' => 'O campo :attribute não deve conter números.',
            'matricula.regex' => 'O campo :attribute deve conter somente números.',

            'presos.*.nome.regex' => 'O campo :attribute não deve conter números.',
            'presos.*.nome_social.regex' => 'O campo :attribute não deve conter números.',
            'presos.*.mae.regex' => 'O campo :attribute não deve conter números.',
            'presos.*.pai.regex' => 'O campo :attribute não deve conter números.',
            'presos.*.matricula.regex' => 'O campo :attribute deve conter somente números.',
        ];
    }

    /**
     * Retorna um array de mensagens para uso do Validator
     */
    static function getAttributeNamesValidate(): array
    {
        return [
            'presos.*.nome' => 'nome',
            'presos.*.mae' => 'mae',
            'presos.*.pai' => 'pai',
            'presos.*.matricula' => 'matricula',
        ];
    }

    /**
     * Efetua uma validação dos inputs da request enviada
     */
    static function validacaoRequest(Request $request, array $rules, array $attributeNames = [], array $messages = [], $options = [])
    {
        if (!count($messages)) {
            $messages = CommonsFunctions::getMessagesValidate();
        } else {
            $messages = array_merge($messages, CommonsFunctions::getMessagesValidate());
        }

        if (!count($attributeNames)) {
            $attributeNames = CommonsFunctions::getAttributeNamesValidate();
        } else {
            $attributeNames = array_merge($attributeNames, CommonsFunctions::getAttributeNamesValidate());
        }

        // Aqui se verifica se deve retornar o response, caso haja erro. Se for request api, retornará automaticamente em json.
        // Caso contrário, sendo request web, retornará a classe RestResponse para que seja tratada e renderizada na tela, a não ser que envie o 'autoResponse' como true.
        $autoResponse = $options['autoResponse'] ?? $request->is('api/*') ? true : false;

        // Valide os dados recebidos da requisição
        $validator = Validator::make($request->all(), $rules, $messages, $attributeNames);

        if ($validator->fails()) {
            // Gerar um log
            $mensagem = "A requisição não pôde ser processada.";
            $traceId = CommonsFunctions::generateLog($mensagem . "| Request: " . json_encode($request->input()) . "Validator: " . json_encode($validator->errors()));

            // Se a validação falhar, retorne os erros em uma resposta JSON com código 422 (Unprocessable Entity)
            $response = RestResponse::createGenericResponse(["errors" => $validator->errors()], 422, $mensagem, $traceId);
            if ($autoResponse) {
                return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
            }

            return $response;
        }

        return true;
    }

    static function retornaErroQueImpedemProcessamento422($arrErrors)
    {
        // Erros que impedem o processamento
        if (count($arrErrors)) {
            // Gerar um log
            $codigo = 422;
            $mensagem = "A requisição não pôde ser processada.";
            $traceId = CommonsFunctions::generateLog("$codigo | $mensagem | Errors: " . json_encode($arrErrors));

            $response = RestResponse::createGenericResponse(["errors" => $arrErrors], $codigo, $mensagem, $traceId);
            return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
        }
    }

    static function formatarDataTimeZonaAmericaSaoPaulo($value)
    {
        if ($value) {
            return Carbon::parse($value)->timezone(config('app.timezone'))->toDateTimeString();
        }

        return null;
    }

    static function getIdUsuarioNoTenant()
    {

        $user = null;
        $userId = auth()->user()->id;
        $user = UserTenantDomain::where('user_id', $userId)->where('tenant_id', tenant('id'))->first();

        if (!$user) {
            $adminUuid = UUIDsHelpers::getAdmin();

            if ($userId == $adminUuid) {
                return UUIDsHelpers::getAdminUserTenantDomain();
            }

            //Colocar verificação para se o usuário tem a permissão de suporte do tenant, para poder colocar o id dele que é do tenant admin
        } else {
            return $user->id;
        }
        return null;
    }

    static function inserirInfoCreated($novo)
    {
        $novo->created_user_id = self::getIdUsuarioNoTenant();
        $novo->created_ip = UserInfo::get_ip();
        $novo->created_at = self::formatarDataTimeZonaAmericaSaoPaulo(now());
        $novo->updated_at = null;
    }

    static function inserirInfoUpdated($resource)
    {
        $resource->updated_user_id = self::getIdUsuarioNoTenant();
        $resource->updated_ip = UserInfo::get_ip();
        $resource->updated_at = self::formatarDataTimeZonaAmericaSaoPaulo(now());
    }

    static function inserirInfoDeleted($resource)
    {
        $resource->deleted_user_id = self::getIdUsuarioNoTenant();
        $resource->deleted_ip = UserInfo::get_ip();
        $resource->deleted_at = self::formatarDataTimeZonaAmericaSaoPaulo(now());
    }

    /**
     * Formata uma string para maiúsculas, minúsculas ou título com base no tipo fornecido.
     *
     * @param string $string A string a ser formatada.
     * @param int $type O tipo de formatação a ser aplicado. O padrão é 1 para maiúsculas.
     *                 1: Maiúsculas
     *                 2: Minúsculas
     *                 3: Título
     * @return string A string formatada.
     */
    static function formatarStringUTF8($string, $type = 1)
    {
        switch ($type) {
            case 1:
                return mb_convert_case($string, MB_CASE_UPPER, 'UTF-8');
            case 2:
                return mb_convert_case($string, MB_CASE_LOWER, 'UTF-8');
            case 3:
                return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
            default:
                return $string;
        }
    }

    /**
     * Remove a pontuação do texto fornecido usando uma regex fornecida ou uma regex padrão.
     *
     * @param string $string O texto de entrada do qual a pontuação será removida.
     * @param array $options  Um array de opções:
     *                 - 'regex': A expressão regular para corresponder à pontuação (padrão: null, para usar a expressão regular padrão).
     *                 - 'espacoBranco': Um sinalizador para incluir espaços em branco na regex (padrão: falso).
     * @return string O texto de entrada sem pontuação.
     */
    public static function removePontuacaoDeTexto($string, array $options = [])
    {
        // Obtem a regex enviada ou por padrão define a expressão regular para corresponder apenas a letras, números
        $regex = isset($options['regex']) ? $options['regex'] : null;

        if (!$regex) {
            $espacoBranco = isset($options['espacoBranco']) ? $options['espacoBranco'] : false;
            if ($espacoBranco) {
                $regex = '/[^a-zA-Z0-9\s]/';
            } else {
                $regex = '/[^a-zA-Z0-9]/';
            }
        }

        $stringSemPontuacao = preg_replace($regex, '', $string);

        return $stringSemPontuacao;
    }

    /**
     * Remove espaços duplicados de uma string.
     *
     * @param string $string A string de entrada.
     * @throws Exception Se o argumento $string não for uma string.
     * @return string A string de entrada com espaços duplicados removidos.
     */
    public static function removeEspacosDuplos($string)
    {
        if (!is_string($string)) {
            throw new Exception('O argumento $string deve ser uma string.');
        }

        $stringSemPontuacao = preg_replace('/ {2,}/', ' ', $string);

        return $stringSemPontuacao;
    }

    /**
     * Retorna um array de parâmetros para uma consulta LIKE com base nos dados fornecidos.
     *
     * @param array $dados Os dados usados para gerar os parâmetros.
     *                    Deve conter uma chave 'parametros_like' com um valor de array.
     *                    O array 'parametros_like' deve conter as seguintes chaves:
     *                    - 'curinga_inicio_bln' (booleano)
     *                    - 'curinga_inicio_caractere' (string)
     *                    - 'curinga_final_bln' (booleano)
     *                    - 'curinga_final_caractere' (string)
     *                    - 'conectivo' (string)
     * @return array Os parâmetros gerados para a consulta LIKE.
     *              O array contém as seguintes chaves:
     *              - 'curinga_inicio_bln' (booleano)
     *              - 'curinga_inicio_caractere' (string)
     *              - 'curinga_final_bln' (booleano)
     *              - 'curinga_final_caractere' (string)
     *              - 'conectivo' (string)
     */
    public static function retornaCamposParametrosLike(array $dados)
    {
        $parametros = [
            'curinga_inicio_bln' => false,
            'curinga_inicio_caractere' => '',
            'curinga_final_bln' => false,
            'curinga_final_caractere' => '',
            'conectivo' => 'ilike',
        ];

        $parametrosLike = $dados['parametros_like'] ?? [];
        if (
            isset($parametrosLike['curinga_inicio_bln']) && in_array($parametrosLike['curinga_inicio_bln'], [true, 1]) ||
            isset($parametrosLike['curinga_final_bln']) && in_array($parametrosLike['curinga_final_bln'], [true, 1])
        ) {
            $parametros['curinga_inicio_caractere'] = isset($parametrosLike['curinga_inicio_bln']) && in_array($parametrosLike['curinga_inicio_bln'], [true, 1]) ?
                (isset($parametrosLike['curinga_inicio_caractere']) ? in_array($parametrosLike['curinga_inicio_caractere'], ['%', '_']) : '%') : '';
            $parametros['curinga_final_caractere'] = isset($parametrosLike['curinga_final_bln']) && in_array($parametrosLike['curinga_final_bln'], [true, 1]) ?
                (isset($parametrosLike['curinga_final_caractere']) ? in_array($parametrosLike['curinga_final_caractere'], ['%', '_']) : '%') : '';
            $parametros['conectivo'] = $parametrosLike['conectivo'] ?? 'ilike';
        }

        return $parametros;
    }

    /**
     * Retorna um array de texto para filtragem com base nos dados fornecidos.
     *
     * @param array $dados Os dados contendo o texto a ser filtrado.
     *                     Deve conter as seguintes chaves:
     *                     - 'texto' (string): O texto a ser filtrado.
     *                     - 'texto_tratamento' (array, opcional): As opções de tratamento para o texto.
     *                       Deve conter as seguintes chaves:
     *                       - 'tratamento' (string, opcional): O tipo de tratamento de tratamento.
     *                         Padrão é 'texto_todo'.
     * @return array O array de texto para filtragem.
     *               Se a chave 'tratamento' em 'texto_tratamento' for definida como 'texto_dividido',
     *               o texto será dividido em um array de palavras usando o método 'removeEspacosDuplos'
     *               para remover os espaços duplos e divirtir o texto no espaço de palavras.
     *               Caso contrário, o texto original será retornado como um array.
     */
    public static function retornaArrayTextoParaFiltros($dados)
    {
        $retorno = [$dados['texto']];
        $textoTratamento = isset($dados['texto_tratamento']) ? $dados['texto_tratamento'] : [];
        $tratamento = isset($textoTratamento['tratamento']) ? $textoTratamento['tratamento'] : 'texto_todo';
        switch ($tratamento) {
            case 'texto_dividido':
                $retorno = trim($dados['texto']) ? explode(' ', CommonsFunctions::removeEspacosDuplos($dados['texto'])) : [];
                break;

            default:
                $retorno = [$dados['texto']];
                break;
        }
        return $retorno;
    }
}
