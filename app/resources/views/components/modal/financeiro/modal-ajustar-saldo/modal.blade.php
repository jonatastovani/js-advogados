@php
    $sufixo = 'ModalAjustarSaldo';
@endphp

<div class="modal fade" id="{{ $sufixo }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate"data-title-default="Ajustar Saldo">Ajustar Saldo</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <form class="formRegistration">
                <div class="modal-body">
                    <div class="row">
                        <div class="col mt-2">
                            <div class="row row-cols-1 row-cols-sm-2 align-items-end">
                                <div class="col">
                                    <div class="form-text mt-0">Conta</div>
                                    <p class="pNomeConta"></p>
                                </div>
                                <div class="col">
                                    <div class="form-text mt-0">Saldo atual</div>
                                    <p class="pSaldoAtual"></p>
                                </div>
                                <div class="col">
                                    <div class="form-text mt-0">Última movimentação</div>
                                    <p class="pUltimaMovimentacao"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row row-cols-1 row-cols-sm-2 flex-row-reverse">
                        <div class="col">
                            <label for="novo_saldo{{ $sufixo }}" class="form-label">Novo saldo*</label>
                            <div class="input-group">
                                <div class="input-group-text"><label for="novo_saldo{{ $sufixo }}">R$</label>
                                </div>
                                <input type="text" id="novo_saldo{{ $sufixo }}" name="novo_saldo"
                                    class="form-control text-end campo-monetario">
                            </div>
                        </div>
                    </div>

                    <x-pagina.info-campos-obrigatorios />

                    <div class="row">
                        <div class="col-12 text-end mt-2">
                            <button type="submit" class="btn btn-outline-success btn-save" style="min-width: 7rem;">
                                Salvar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@component('components.api.api-routes', [
    'routes' => [
        'baseContas' => route('api.tenant.conta'),
        'baseAtualizarSaldoConta' => route('api.financeiro.movimentacao-conta.atualizar-saldo-conta'),
    ],
])
@endcomponent
