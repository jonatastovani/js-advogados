<?php

namespace App\Http\Controllers\View\Documento;

use App\Http\Controllers\Controller;
use App\Models\Tenant\DocumentoModeloTenant;
use Illuminate\Http\Request;

class DocumentoController extends Controller
{

    public function documentoModeloIndex()
    {
        return view('secao.documento.modelo.index');
    }

    public function documentoModeloForm()
    {
        return view('secao.documento.modelo.form');
    }

    public function documentoModeloFormEditar(Request $request)
    {
        $resource = DocumentoModeloTenant::find($request->uuid);
        if ($resource) {
            return view('secao.documento.modelo.form', compact('resource'));
        }
        return view('secao.documento.modelo.form');
    }
}
