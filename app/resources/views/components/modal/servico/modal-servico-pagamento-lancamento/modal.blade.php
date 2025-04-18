@php
    $sufixo = 'ModalServicoPagamentoLancamento';
@endphp

<div class="modal fade" id="{{ $sufixo }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate" data-title-default="Lançamento">Lançamento</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row formRegistration">
                    <div class="col">

                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 align-items-end">
                            <div class="col">
                                <div class="form-text mt-0">Data de vencimento</div>
                                <input type="date" name="data_vencimento" id="data_vencimento{{ $sufixo }}"
                                    class="form-control text-center campos-personalizar-lancamento" readonly>
                            </div>
                            <div class="col">
                                <div class="form-text mt-0">Valor</div>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <label for="valor_esperado{{ $sufixo }}">R$</label>
                                    </div>
                                    <input type="text" name="valor_esperado" id="valor_esperado{{ $sufixo }}"
                                        class="form-control text-end campos-personalizar-lancamento" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="forma_pagamento_id{{ $sufixo }}" class="form-label">Forma de
                                    pagamento</label>
                                <div class="input-group">
                                    <select name="forma_pagamento_id" id="forma_pagamento_id{{ $sufixo }}"
                                        class="form-select">
                                        <option value="0">Selecione</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-primary openModalFormaPagamento"><i
                                            class="bi bi-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mt-2">
                                <label for="observacao{{ $sufixo }}" class="form-label">Observação</label>
                                <input type="text" id="observacao{{ $sufixo }}" name="observacao"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-end mt-2">
                                <button type="submit" class="btn btn-outline-success btn-save"
                                    style="min-width: 7rem;">
                                    Salvar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('modals')
    <x-modal.tenant.modal-forma-pagamento-tenant.modal />
@endpush

@push('scripts')
    @component('components.api.api-routes', [
        'routes' => [
            'baseFormaPagamento' => route('api.tenant.forma-pagamento'),
        ],
    ])
    @endcomponent
@endpush
