<p class="text-end display-6 mb-0">Valor do Serviço: R$ <span id="valorFinal{{ $sufixo }}">0,00</span></p>

<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 text-end">
    <p class="mb-0">Total dos pagamentos: R$ <span id="valorServico{{ $sufixo }}">0,00</span></p>
    <p class="mb-0">Lançamentos cancelados: R$ <span id="totalCancelado{{ $sufixo }}">0,00</span></p>
    <p class="mb-0">Lançamentos aguardando: R$ <span id="totalAguardando{{ $sufixo }}">0,00</span></p>
    <p class="mb-0">Lançamentos em análise: R$ <span id="totalEmAnalise{{ $sufixo }}">0,00</span></p>
    <p class="mb-0">Lançamentos liquidado: R$ <span id="totalLiquidado{{ $sufixo }}">0,00</span></p>
    <p class="mb-0">Lançamentos inadimplente: R$ <span id="totalInadimplente{{ $sufixo }}">0,00</span></p>
</div>

<div class="row text-end">
    <div class="col mt-2">
        <button type="button" class="btn btn-outline-primary btn-sm"
            id="btnInserirPagamento{{ $sufixo }}">Inserir Pagamento</button>
        <button type="button" class="btn btn-outline-primary btn-sm"
            id="atualizarPagamentos{{ $sufixo }}">Atualizar Pagamentos</button>
    </div>
</div>

<div id="divPagamento{{ $sufixo }}" class="row flex-column g-2 mt-2"></div>
