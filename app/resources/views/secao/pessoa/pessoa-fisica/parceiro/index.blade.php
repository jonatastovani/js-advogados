@php
    $sufixo = 'PageParceiroPFIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Listagem de Parceiros',
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
                    'nome' => ['nome' => 'Nome'],
                    'mae' => ['nome' => 'Mãe'],
                    'pai' => ['nome' => 'Pai'],
                    'documento' => ['nome' => 'Documento'],
                ],
                'direcaoConsultaChecked' => 'asc',
                'arrayCamposChecked' => ['nome', 'documento'],
                'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                'dadosSelectFormaBusca' => ['selecionado' => 'iniciado_por'],
                'arrayCamposOrdenacao' => [
                    'nome' => ['nome' => 'Nome'],
                    'created_at' => ['nome' => 'Data cadastro'],
                ],
                'camposExtras' => [
                    [
                        'tipo' => 'radio',
                        'nome' => 'ativo_bln',
                        'opcoes' => [
                            [
                                'id' => "rbStatusTodos{$sufixo}",
                                'valor' => '',
                                'label' => 'Todos os status',
                                'checked' => true,
                                'title' => 'Todos os status',
                            ],
                            [
                                'id' => "rbStatusAtivo{$sufixo}",
                                'valor' => '1',
                                'label' => 'Ativos',
                                'title' => 'Parceiros ativos',
                            ],
                            [
                                'id' => "rbStatusInativo{$sufixo}",
                                'valor' => '0',
                                'label' => 'Inativos',
                                'title' => 'Parceiros inativos',
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
            <a href="{{ route('pessoa.pessoa-fisica.parceiro.form') }}" class="btn btn-outline-primary">Cadastrar</a>
        </div>
    </div>  

    <div class="table-responsive mt-2 flex-fill">
        <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                    <th class="text-nowrap">Nome</th>
                    <th class="text-nowrap">Mãe</th>
                    <th class="text-nowrap">Pai</th>
                    <th class="text-nowrap">Estado Civil</th>
                    <th class="text-nowrap">Escolaridade</th>
                    <th class="text-nowrap">Gênero</th>
                    <th class="text-nowrap" title="Data de Nascimento">Data Nasc.</th>
                    <th class="text-nowrap">Naturalidade</th>
                    <th class="text-nowrap">Nacionalidade</th>
                    <th class="text-nowrap">Perfis</th>
                    <th class="text-nowrap">Status</th>
                    <th class="text-nowrap">Cadastro</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <x-consulta.section-paginacao.componente :sufixo="$sufixo" />

@endsection

@push('modals')
    <x-modal.financeiro.modal-conta.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/pessoa/pessoa-fisica/parceiro/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'basePessoaFisica' => route('api.pessoa.pessoa-fisica'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'baseFrontPessoaFisicaParceiroForm' => route('pessoa.pessoa-fisica.parceiro.form'),
            // 'baseFrontImpressao' => route('financeiro.movimentacao-conta.impressao'),
        ],
    ])
    @endcomponent
@endpush
