@php
    $sufixo = 'PagePermissoes';
    $paginaDados = new Illuminate\Support\Fluent([
        'home' => route('admin.index'),
        'nome' => 'Permissões',
        'descricao' => [
            [
                'texto' => 'Esta seção é dedicada ao gerenciamento das permissões do sistema. Aqui, você pode criar, organizar e
        configurar as permissões de forma escalonada para diferentes níveis de usuários e funções dentro do sistema.',
            ],
            [
                'texto' => 'Utilize esta página para definir os dados das permissões seguindo o padrão de desenvolvimento,
        extraindo aqui a estrutura para inserir nas funções e enums.',
                'class_add' => 'text-danger',
            ],
        ],
    ]);
    Session::put('paginaDados', $paginaDados);
@endphp

@extends('layouts.layout')
@section('title', $paginaDados->nome)

@section('conteudo')

    @component('components.pagina.dados-pagina', ['paginaDados' => $paginaDados])
    @endcomponent
    <div class="row">
        @php
            $dados = new Illuminate\Support\Fluent([
                'camposFiltrados' => [
                    'id' => ['nome' => 'ID'],
                    'nome' => ['nome' => 'Nome'],
                    'nome_completo' => ['nome' => 'Nome completo'],
                    'descricao' => ['nome' => 'Descrição'],
                ],
                'arrayCamposChecked' => ['nome', 'nome_completo', 'descricao'],
                'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                'dadosSelectFormaBusca' => ['selecionado' => 'qualquer_incidencia'],
            ]);
        @endphp
        <x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />
    </div>

    <div class="row">
        <div class="col mt-2">
            <button id="btnInserirPermissao" type="button" class="btn btn-outline-primary">Inserir Permissão</button>
        </div>
    </div>

    <div class="table-responsive mt-2">
        <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                    <th class="text-center">ID</th>
                    <th>Nome</th>
                    <th class="text-nowrap">Nome Completo</th>
                    <th>Descrição</th>
                    <th>Grupo</th>
                    <th>Modulo</th>
                    <th>Ativo</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <x-consulta.section-paginacao.componente :sufixo="$sufixo" />

@endsection


@push('modals')
    <x-modal.admin.modal-permissao.modal />
    <x-modal.admin.modal-code.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/admin/permissoes/permissoes.js')
    @component('components.api.api-routes', [
        'routes' => [
            'basePermissoes' => route('api.admin.permissoes'),
        ],
    ])
    @endcomponent
@endpush
