@php
    $sufixo = 'PagePessoaFisicaFormUsuario';
    $recurso = isset($recurso) ? $recurso : null;
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => $recurso ? 'Editar Usuário' : 'Cadastrar Usuário',
        'descricao' => [
            [
                'texto' => 'Cadastro de usuário do sistema.',
            ],
        ],
        'perfil_tipo' => 'usuario',
    ]);
    Session::put('paginaDados', $paginaDados);
@endphp

@extends('layouts.layout')
@section('title', $paginaDados->nome)

@section('conteudo')

    @component('components.pagina.dados-pagina', ['paginaDados' => $paginaDados])
    @endcomponent

    @include('secao.pessoa.pessoa-fisica.form.body')

@endsection

{{-- Inserir as rotas api e os modais --}}
@include('secao.pessoa.pessoa-fisica.form.push')

@push('modals')
    <x-modal.pessoa.modal-selecionar-usuario-domains.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/pessoa/pessoa-fisica/usuario/form.js')
    @component('components.pagina.front-routes', [
        'routes' => [
            'frontRedirectForm' => route('pessoa.pessoa-fisica.usuario.index'),
        ],
    ])
    @endcomponent
@endpush
