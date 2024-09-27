@extends('layouts.layout')
@section('title', 'Home')

@section('conteudo')
    <div class="row">
        <div class="col-12 text-center">
            <h1 class="display-6">Bem-vindo ao Módulo Inteligência</h1>
            <p class="lead">Este é o módulo onde você encontrará todas as funcionalidades para gerenciar e analisar
                informações de maneira eficiente.</p>
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
