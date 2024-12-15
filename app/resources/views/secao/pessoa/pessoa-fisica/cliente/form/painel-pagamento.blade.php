<div class="row text-end">
    <div class="col-12 mt-2">
        <span class="display-6">Valor do Serviço: R$ <span id="valorServico{{ $sufixo }}">0,00</span></span>
        <p class="mb-0">Total aguardando: R$ <span id="totalAguardando{{ $sufixo }}">0,00</span></p>
        <p class="mb-0">Total em análise: R$ <span id="totalEmAnalise{{ $sufixo }}">0,00</span></p>
        <p class="mb-0">Total liquidado: R$ <span id="totalLiquidado{{ $sufixo }}">0,00</span></p>
        <p class="mb-0">Total inadimplente: R$ <span id="totalInadimplente{{ $sufixo }}">0,00</span></p>
    </div>
</div>
<div class="row text-end">
    <div class="col mt-2">
        <button type="button" class="btn btn-outline-primary btn-sm"
            id="btnInserirPagamento{{ $sufixo }}">Inserir Pagamento</button>
    </div>
</div>

<div id="divPagamento{{ $sufixo }}" class="row flex-column g-2 mt-2"></div>
