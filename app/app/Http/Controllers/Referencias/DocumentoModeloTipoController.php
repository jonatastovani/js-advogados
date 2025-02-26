<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referencias\DocumentoModeloTipo\DocumentoModeloTipoFormRequestIndex;
use App\Http\Requests\Referencias\DocumentoModeloTipo\DocumentoModeloTipoFormRequestShow;
use App\Http\Requests\Referencias\DocumentoModeloTipo\DocumentoModeloTipoFormRequestStore;
use App\Http\Requests\Referencias\DocumentoModeloTipo\DocumentoModeloTipoFormRequestUpdate;
use App\Services\Referencias\DocumentoModeloTipoService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class DocumentoModeloTipoController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public DocumentoModeloTipoService $service) {}

    public function index(DocumentoModeloTipoFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    // public function store(DocumentoModeloTipoFormRequestStore $formRequest)
    // {
    //     $fluentData = $this->makeFluent($formRequest->validated());
    //     return $this->retornoPadrao($this->service->store($fluentData));
    // }

    public function show(DocumentoModeloTipoFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    // public function update(DocumentoModeloTipoFormRequestUpdate $formRequest)
    // {
    //     $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
    //     return $this->retornoPadrao($this->service->update($fluentData));
    // }
}
