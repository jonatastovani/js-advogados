<?php

namespace App\Http\Controllers\GPU;

use App\Common\RestResponse;
use App\Http\Controllers\Controller;
use App\Services\GPU\FuncionarioGPUService;
use Illuminate\Http\Request;

class FuncionarioGPUController extends Controller
{
    protected $service;

    public function __construct(FuncionarioGPUService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $resource = $this->service->buscarRecursoPorId($request->id, ['responseAuto' => true]);

        $response = RestResponse::createSuccessResponse($resource->toArray(), 200);
        return response()->json($response->toArray(), $response->getStatusCode());
    }
}
