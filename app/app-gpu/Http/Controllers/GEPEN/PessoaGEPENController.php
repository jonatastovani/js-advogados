<?php

namespace App\Http\Controllers\GEPEN;

use App\Common\RestResponse;
use App\Http\Controllers\Controller;
use App\Services\GEPEN\PessoaGEPENService;
use Illuminate\Http\Request;

class PessoaGEPENController extends Controller
{

    public function __construct(public PessoaGEPENService $service)
    {
    }
    public function show(Request $request)
    {
        $resource = $this->service->buscarRecursoPorId($request->id, ['responseAuto' => true]);

        $response = RestResponse::createSuccessResponse($resource->toArray(), 200);
        return response()->json($response->toArray(), $response->getStatusCode());
    }

    public function showServidor(Request $request)
    {
        $resource = $this->service->buscarRecursoPorId($request->id, ['responseAuto' => true]);
        if ($resource) {
            $resource->load('servidor');
        }

        $response = RestResponse::createSuccessResponse($resource->toArray(), 200);
        return response()->json($response->toArray(), $response->getStatusCode());
    }
}
