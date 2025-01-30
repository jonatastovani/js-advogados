<div class="modal fade" id="modalFormaPagamentoTenant" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title">Formas de Pagamentos</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                @php
                    $sufixo = 'ModalFormaPagamentoTenant';
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
                                <th>Conta</th>
                                <th>Ativo</th>
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
                    @include('components.modal.tenant.modal-forma-pagamento-tenant.campos-cadastro', [
                        'sufixo' => $sufixo,
                    ])
                </div>
            </div>
        </div>
    </div>
</div>


@push('modals')
    <x-modal.tenant.modal-conta-tenant.modal />
@endpush

@component('components.api.api-routes', [
    'routes' => [
        'baseFormaPagamento' => route('api.tenant.forma-pagamento'),
        'baseContas' => route('api.tenant.conta'),
    ],
])
@endcomponent
