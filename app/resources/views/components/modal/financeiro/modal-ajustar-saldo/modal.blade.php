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
            <form class="modal-body formRegistration">
                <div class="row row-cols-1 row-cols-sm-2 align-items-end">
                    <div class="col">
                        <div class="form-text mt-0">Conta</div>
                        <p class="pNomeConta"></p>
                    </div>
                    <div class="col">
                        <div class="form-text mt-0">Saldo total</div>
                        <p class="pSaldoAtual"></p>
                    </div>
                </div>
                <div class="row row-cols-1 row-cols-sm-2">
                    <div class="col">
                        <label for="domain_id{{ $sufixo }}" class="form-label">Unidade</label>
                        <select name="domain_id" class="form-select" id="domain_id{{ $sufixo }}">
                            <option value="0">Selecione</option>
                        </select>
                    </div>
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

                <div class="row">
                    <div class="col mt-2">
                        <div class="form-text mt-0">Última movimentação</div>
                        <p class="pUltimaMovimentacao mb-0"></p>
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
            </form>
        </div>
    </div>
</div>

@component('components.api.api-routes', [
    'routes' => [
        'baseAtualizarSaldoConta' => route('api.financeiro.movimentacao-conta.atualizar-saldo-conta'),
        'baseContas' => route('api.tenant.conta'),
        'baseDomains' => route('api.tenant.domains'),
    ],
])
@endcomponent
