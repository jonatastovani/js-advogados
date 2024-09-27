<?php

namespace App\Http\Controllers;

use App\Common\RestResponse;
use App\Helpers\FotoHelper;
use App\Models\GEPEN\ServidorPessoaGEPEN;
use App\Models\GEPEN\ServidorPessoaImagemGEPEN;
use App\Services\Foto\FotoManagerGPUService;
use App\Services\Foto\FotoManagerSISDRHUService;
use App\Traits\CommonsControllerMethodsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

class FotoController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public FotoHelper $fotoHelper) {}

    public function buscarFotoPresoId(Request $request)
    {
        $response = new Fluent($this->fotoHelper->buscarFotoPreso($request->id));
        if ($response->code && $response->code == 200) {
            return $this->retornoPadrao($response->response);
        }
        return $this->retornoPadrao(RestResponse::createErrorResponse($response->code, $response->message));
    }

    public function buscarFotoFuncionarioId(Request $request)
    {
        // Solicitar acesso ao servidor do GEPEN
        $response = ServidorPessoaImagemGEPEN::where('seim_pess_id', $request->id)->first();
        if ($response) {
            $foto =  app(FotoManagerSISDRHUService::class)->getConsultarImagem('FOTOSERVIDORES', $response->seim_endereco);
            RestResponse::createTestResponse([$foto]);
        }
        return $this->retornoPadrao(RestResponse::createErrorResponse(404, 'Foto de servidor não encontrada.'));
    }
}
