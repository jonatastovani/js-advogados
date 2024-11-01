<?php

namespace App\Traits;

use App\Common\RestResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

trait CommonsControllerMethodsTrait
{
    public function retornoPadrao($response)
    {
        if ($response instanceof RestResponse) {
            return $response->autoResponse();
        }
        return RestResponse::createSuccessResponse($response)->autoResponse();
    }

    /**
     * Converte os dados de uma Request ou de um array validado para um objeto Fluent.
     *
     * @param Request|array $input
     * @param Request|null $request Se o input for um array, pode passar o Request para pegar parâmetros de rota.
     * @param bool $includeParamsRoute Se os parâmetros da rota devem ser incluídos
     * @return Fluent
     */
    protected function makeFluent($input, Request $request = null, $includeParamsRoute = true): Fluent
    {
        // Se o input for um array, usa os dados diretamente, caso contrário, trata a Request
        if (is_array($input)) {
            // Se for array, mescla com os parâmetros da rota, caso seja necessário
            if ($includeParamsRoute && $request) {
                $requestData = array_merge($input, $request->route()->parameters());
            } else {
                $requestData = $input;
            }
        } else {
            // Se for Request, mescla todos os dados da requisição com os parâmetros da rota
            $requestData = array_merge($input->all(), $input->route()->parameters());
        }

        // Retorna um objeto Fluent com os dados combinados
        return new Fluent($requestData);
    }
}
