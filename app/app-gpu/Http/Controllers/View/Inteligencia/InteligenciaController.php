<?php

namespace App\Http\Controllers\View\Inteligencia;

use App\Http\Controllers\Controller;
use App\Models\GPU\Inteligencia\InformacaoSubjetiva;
use Illuminate\Http\Request;

class InteligenciaController extends Controller
{

    public function index()
    {
        return view('modulos.inteligencia.index', ['dados' => 'Inteligencia']);
    }

    public function informacaoSubjetivaIndex()
    {
        return view('modulos.inteligencia.informacao-subjetiva.index');
    }

    public function informacaoSubjetivaForm()
    {
        return view('modulos.inteligencia.informacao-subjetiva.form.form');
    }

    public function informacaoSubjetivaFormEditar(Request $request)
    {
        $recurso = InformacaoSubjetiva::find($request->uuid);
        if ($recurso) {
            return view('modulos.inteligencia.informacao-subjetiva.form.form');
        }
        return view('errors.recurso_nao_encontrado');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
