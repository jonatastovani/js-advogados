<?php

namespace App\Traits;

use App\Common\CommonsFunctions;
use App\Http\Requests\Comum\Consulta\PostConsultaFiltroFormRequestBase;
use Illuminate\Http\Request;

trait CommonsConsultaControllerTrait
{

    public function select2(Request $request)
    {
        $rules = [
            'text' => 'required|string|min:3',
        ];
        CommonsFunctions::validacaoRequest($request, $rules);
        return $this->retornoPadrao($this->service->select2($request)->toArray());
    }

    public function postConsultaFiltros(PostConsultaFiltroFormRequestBase $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltros($fluentData));
    }
}
