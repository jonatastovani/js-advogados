<div class="modal fade" id="modalConta" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title">Contas</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                @php
                    $sufixo = 'ModalConta';
                    $dados = new Illuminate\Support\Fluent([
                        'camposFiltrados' => [
                            'nome' => ['nome' => 'Nome'],
                            'descricao' => ['nome' => 'Descrição'],
                            'banco' => ['nome' => 'Banco'],
                        ],
                        'arrayCamposChecked' => ['nome', 'descricao', 'banco'],
                        'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                        'dadosSelectFormaBusca' => ['selecionado' => 'iniciado_por'],
                        'preset_tamanho' => 'md',
                    ]);
                @endphp
                <x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />

                <div class="table-responsive mt-2">
                    <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover tableData">
                        <thead>
                            <tr>
                                <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                                <th>Nome</th>
                                <th>Saldo</th>
                                <th>Banco</th>
                                <th>Subtipo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <x-consulta.section-paginacao.componente :sufixo="$sufixo" />

                <div class="row">
                    <div class="col-12 mt-2 divBtnAdd">
                        <button type="button" class="btn-new-register btn btn-outline-primary"
                            style="min-width: 7rem;">Adicionar</button>
                    </div>
                </div>
                <div class="divRegistrationFields mt-2" style="display: none;">
                    @include('components.modal.financeiro.modal-conta.campos-cadastro', [
                        'sufixo' => $sufixo,
                    ])
                </div>
            </div>
        </div>
    </div>
</div>

@component('components.api.api-routes', [
    'routes' => [
        'baseContas' => route('api.financeiro.conta'),
        'baseContasSubtipo' => route('api.referencias.conta-subtipo'),
        'baseContasStatus' => route('api.referencias.conta-status-tipo'),
    ],
])
@endcomponent