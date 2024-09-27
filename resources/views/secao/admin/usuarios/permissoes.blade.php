@php
    $paginaDados = new Illuminate\Support\Fluent(['home' => route('admin.index'), 'nome' => 'Permissões']);
    Session::put('paginaDados', $paginaDados);
@endphp

@extends('layouts.layout')
@section('title', $paginaDados->nome)

@section('conteudo')
    <div class="row">
        <div class="col-12">
            <h1 class="display-6 text-end">{{ $paginaDados->nome }}</h1>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <h2>Funcionalidades Disponíveis</h2>
            <ul class="list-group">
                <li class="list-group-item">Cadastro de Informações Subjetivas</li>
                <li class="list-group-item">Consultas cruzadas e avançadas</li>
                <li class="list-group-item">Exportar Relatórios de Informações</li>
                <li class="list-group-item">Monitoramento em Tempo Real</li>
            </ul>
        </div>
    </div>
@endsection
