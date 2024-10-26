@php
    $sufixo = 'PageServicoParticipacaoPresetForm';
    $recurso = isset($recurso) ? $recurso : null;
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => $recurso ? 'Editar Preset: ' . $recurso->nome : 'Cadastrar Preset de Participação',
        'descricao' => [
            [
                'texto' =>
                    'Cadastro de presets de coparticipantes para agilizar o preenchimento de informações em pagamentos e lançamentos.',
            ],
        ],
    ]);
    Session::put('paginaDados', $paginaDados);

    $disabledNovoRegistro = true;
    if ($recurso) {
        $disabledNovoRegistro = false;
    }
@endphp

@extends('layouts.layout')
@section('title', $paginaDados->nome)

@section('conteudo')

    @component('components.pagina.dados-pagina', ['paginaDados' => $paginaDados])
    @endcomponent

    @include('secao.servico.participacao-preset.form.painel-dados-preset')

    @include('secao.servico.participacao-preset.form.painel-dados-participacao-relacao')

    <div class="row text-end">
        <div class="col mt-2">
            <button type="submit" id="btnSave{{ $sufixo }}" class="btn btn-outline-success btn-save w-50" style="max-width: 7rem">
                Salvar
            </button>
        </div>
    </div>

@endsection

@push('modals')
    <x-modal.pessoa.modal-pessoa.modal />
    <x-modal.comum.modal-nome.modal />
    <x-modal.servico.modal-servico-participacao.modal />
    <x-modal.tenant.modal-servico-participacao-tipo-tenant.modal />
    <x-modal.pessoa.modal-selecionar-perfil.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/servico/participacao/form.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseParticipacaoPreset' => route('api.servico'),
            'baseServicoParticipacaoTipoTenant' => route('api.tenant.servico-participacao-tipo'),
            ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'frontRedirect' => route('servico.index'),
            'frontRedirectForm' => route('servico.form'),
        ],
    ])
    @endcomponent
@endpush
