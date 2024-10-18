@php
    $sufixo = 'PageServicoParticipacaoForm';
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

    @include('secao.servico.participacao.form.painel-dados-preset')

    @include('secao.servico.participacao.form.painel-dados-participacao-relacao')

    <div class="row text-end">
        <div class="col mt-2">
            <button type="submit" id="btnSave{{ $sufixo }}" class="btn btn-outline-success btn-save w-50" style="max-width: 7rem">
                Salvar
            </button>
        </div>
    </div>

@endsection

@push('modals')
    <x-modal.referencias.modal-area-juridica.modal />
    <x-modal.servico.modal-servico-anotacao.modal />
    <x-modal.servico.modal-servico-pagamento.modal />
    <x-modal.servico.modal-selecionar-pagamento-tipo.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/servico/participacao/form.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseServico' => route('api.servico'),
            'baseAreaJuridica' => route('api.referencias.area-juridica'),
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
