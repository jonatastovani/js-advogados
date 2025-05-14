@php
    $sufixo = 'PagePessoaJuridicaIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Listagem de Pessoas Jurídicas',
        'sufixo' => $sufixo,
    ]);
    Session::put('paginaDados', $paginaDados);

    $perfisExistentes = \App\Enums\PessoaPerfilTipoEnum::perfisParaPessoaJuridica();
@endphp

@extends('layouts.layout')
@section('title', $paginaDados->nome)

@section('conteudo')

    @component('components.pagina.dados-pagina', ['paginaDados' => $paginaDados])
    @endcomponent

    <div class="col-12 mt-2">
        <label class="form-label fw-semibold">Tipos de Perfis</label>
        <div class="d-grid gap-1" style="grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));">

            @foreach ($perfisExistentes as $item)
                <div class="form-check form-check-inline">

                    <input class="form-check-input perfis-busca" type="checkbox" name="perfis_selecionados"
                        id="perfil_{{ $item['id'] }}{{ $sufixo }}" value="{{ $item['id'] }}" checked>
                    <label class="form-check-label" for="perfil_{{ $item['id'] }}{{ $sufixo }}"
                        title="{{ $item['descricao'] }}">
                        {{ $item['nome'] }}
                    </label>

                </div>
            @endforeach

        </div>
    </div>

    <div class="row mt-2">
        @php
            $dados = new Illuminate\Support\Fluent([
                'camposFiltrados' => [
                    'razao_social' => ['nome' => 'Razão social'],
                    'nome_fantasia' => ['nome' => 'Nome fantasia'],
                    'responsavel_legal' => ['nome' => 'Responsável legal'],
                    'documento' => ['nome' => 'Documento'],
                ],
                'direcaoConsultaChecked' => 'asc',
                'arrayCamposChecked' => ['razao_social', 'documento'],
                'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                'dadosSelectFormaBusca' => ['selecionado' => 'iniciado_por'],
                'arrayCamposOrdenacao' => [
                    'nome_fantasia' => ['nome' => 'Nome fantasia'],
                    'razao_social' => ['nome' => 'Razão social'],
                    'responsavel_legal' => ['nome' => 'Responsável legal'],
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
                                'title' => 'Clientes ativos',
                            ],
                            [
                                'id' => "rbStatusInativo{$sufixo}",
                                'valor' => '0',
                                'label' => 'Inativos',
                                'title' => 'Clientes inativos',
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
            <a href="{{ route('pessoa.pessoa-juridica.form') }}" class="btn btn-outline-primary">Cadastrar</a>
        </div>
    </div>

    <div class="table-responsive mt-2 flex-fill">
        <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                    <th class="text-nowrap">Razão Social</th>
                    <th class="text-nowrap">Nome Fantasia</th>
                    <th class="text-nowrap">Natureza Jurídica</th>
                    <th class="text-nowrap">Data Fundação</th>
                    <th class="text-nowrap">Capital Social</th>
                    <th class="text-nowrap">Regime Tributário</th>
                    <th class="text-nowrap">Responsável Legal</th>
                    <th class="text-nowrap">CPF Responsável</th>
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
    <x-modal.tenant.modal-conta-tenant.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/pessoa/pessoa-juridica/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'basePessoa' => route('api.pessoa'),
            'basePessoaJuridica' => route('api.pessoa.pessoa-juridica'),
            'basePessoaPerfil' => route('api.pessoa.perfil'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'baseFrontPessoaJuridicaForm' => route('pessoa.pessoa-juridica.form'),
            // 'baseFrontImpressao' => route('financeiro.movimentacao-conta.impressao'),
        ],
    ])
    @endcomponent
@endpush
