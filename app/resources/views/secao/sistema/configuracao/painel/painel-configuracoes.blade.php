<div id="divConfiguracoes{{ $sufixo }}" class="row row-cols-1 row-cols-md-2 row-cols-xxl-3 g-2 flex-fill pt-2">
    {{-- <div class="col">
        <div class="card h-100">
            <div class="card-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch"
                        name="lancamento_liquidado_migracao_sistema_bln"
                        id="lancamento_liquidado_migracao_sistema_bln{{ $sufixo }}">
                    <label class="form-check-label" for="lancamento_liquidado_migracao_sistema_bln{{ $sufixo }}">
                        Ignorar lançamentos antigos no saldo (migração)
                    </label>
                </div>
                <div class="form-text">
                    Se ativado, lançamentos com data anterior ao mês atual serão considerados pagos apenas para
                    histórico, sem movimentar valores. Útil para registros de antes do sistema.
                </div>
            </div>
        </div>
    </div> --}}
    <div class="col">
        <div class="card h-100">
            <div class="card-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch"
                        name="lancamento_liquidado_migracao_sistema_bln"
                        id="lancamento_liquidado_migracao_sistema_bln{{ $sufixo }}">
                    <label class="form-check-label" for="lancamento_liquidado_migracao_sistema_bln{{ $sufixo }}">
                        Exibir opção para considerar lançamentos antigos como liquidados apenas para histórico
                    </label>
                </div>
                <div class="form-text">
                    Quando ativado, ao registrar um pagamento, será exibida a opção de marcar lançamentos anteriores ao
                    mês atual como liquidados apenas para fins históricos, sem movimentar valores. Útil para registros
                    realizados antes da implantação do sistema.
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card h-100">
            <div class="card-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch"
                        name="cancelar_liquidado_migracao_sistema_automatico_bln"
                        id="cancelar_liquidado_migracao_sistema_automatico_bln{{ $sufixo }}">
                    <label class="form-check-label"
                        for="cancelar_liquidado_migracao_sistema_automatico_bln{{ $sufixo }}">
                        Atualizar automaticamente lançamentos como <b>Liquidado (migração sistema)</b>.
                    </label>
                </div>
                <div class="form-text">
                    Ao ativar, sempre que um pagamento não puder ser totalmente excluído, os lançamentos marcados como
                    <b>Liquidado (Migração do Sistema)</b> serão atualizados automaticamente para
                    <b>Cancelado - Liquidado (Migração do Sistema)</b>, deixando de impactar os valores considerados
                    como liquidados.
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card h-100">
            <div class="card-body">
                <p>Exibição de Lançamentos de Serviços por Status no Financeiro → Lançamentos de Serviços</p>
                <button id="btn-ordem-lancamentos-status-servico-listagem" type="button"
                    class="btn btn-outline-primary btn-sm">
                    Reordenar Status
                </button>
                <div class="form-text">
                    Defina a ordem em que os <span class="fst-italic fw-bolder">Lançamentos de Serviços</span> serão
                    exibidos no <a href="{{ route('financeiro.lancamentos-servicos.index') }}"
                        class="fst-italic">Financeiro → Lançamentos de Serviços</a>.
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card h-100">
            <div class="card-body">
                <p>Exibição de Lançamentos de Serviços por Status na Guia Pagamentos em Edição</p>
                <button id="btn-ordem-lancamentos-status-servico-edicao" type="button"
                    class="btn btn-outline-primary btn-sm">
                    Reordenar Status
                </button>
                <div class="form-text">
                    Defina a ordem em que os <span class="fst-italic fw-bolder">Lançamentos de Serviços</span> serão
                    exibidos na guia <span class="fst-italic fw-bolder">Pagamentos</span>, dentro do Serviço em edição.
                </div>
            </div>
        </div>
    </div>

</div>
