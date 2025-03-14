@php
    $sufixo = 'PageDocumentoModeloTenantIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Modelos de Documentos',
        'descricao' => [
            [
                'texto' => 'Cadastro e personalização de modelos de documentos.',
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
                    'titulo' => ['nome' => 'Título'],
                    'descricao' => ['nome' => 'Descrição'],
                    'numero_servico' => ['nome' => 'Número de Serviço'],
                    'nome_participante' => ['nome' => 'Nome Participante'],
                    'nome_grupo' => ['nome' => 'Nome Grupo Participante'],
                    'nome_integrante' => ['nome' => 'Nome Integrante'],
                ],
                'direcaoConsultaChecked' => 'desc',
                'arrayCamposChecked' => ['titulo', 'numero_servico'],
                'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                'dadosSelectFormaBusca' => ['selecionado' => 'iniciado_por'],
                'arrayCamposOrdenacao' => [
                    'created_at' => ['nome' => 'Data cadastro'],
                    'titulo' => ['nome' => 'Título'],
                ],
                'consultaMesAnoBln' => true,
                'camposExtras' => [
                    [
                        'tipo' => 'select',
                        'nome' => 'documento_modelo_tipo_id',
                        'opcoes' => [['id' => 0, 'nome' => 'Todos os tipos']],
                        'input_group' => [
                            'before' => [
                                "<span class='input-group-text'><label for='documento_modelo_tipo_id{$sufixo}' title='Tipos de modelos'>Tipo Mod.</label></span>",
                            ],
                        ],
                    ],
                ],
            ]);
        @endphp
        <x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />
    </div>

    <div class="row">
        <div class="col mt-2">
            <div class="btn-group mt-2 mt-md-0 mt-lg-2 mt-xl-0">
                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    Cadastrar
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a href="{{ route('documento.modelo.index') . '/1/form' }}" class="dropdown-item">
                            Modelo para Serviços
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="table-responsive mt-2 flex-fill">
        <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Tipo</th>
                    <th>Ativo</th>
                    <th>Cadastro</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <x-consulta.section-paginacao.componente :sufixo="$sufixo" />

@endsection


@push('modals')
    {{-- <x-modal.tenant.modal-area-juridica-tenant.modal /> --}}
@endpush

@push('scripts')
    @vite('resources/js/views/documento/modelo/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseDocumentoModeloTenant' => route('api.tenant.documento-modelo-tenant'),
            'baseDocumentoModeloTipo' => route('api.referencias.documento-modelo-tipo'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'baseFront' => route('documento.modelo.index'),
        ],
    ])
    @endcomponent
@endpush
