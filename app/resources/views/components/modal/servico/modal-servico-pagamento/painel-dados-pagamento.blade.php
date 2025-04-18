<div class="row row-cols-1 row-cols-lg-2">
    <div class="col mt-2">
        <label for="forma_pagamento_id{{ $sufixo }}" class="form-label">Forma de pagamento* <i
                class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                data-bs-title="Forma de pagamento que virá preenchida automaticamente antes de confirmar o recebimento do pagamento."></i></label>
        <div class="input-group">
            <select name="forma_pagamento_id" id="forma_pagamento_id{{ $sufixo }}" class="form-select focusRegister">
                <option value="0">Selecione</option>
            </select>
            <button type="button" class="btn btn-outline-primary openModalFormaPagamento"><i
                    class="bi bi-search"></i></button>
        </div>
    </div>
    <div class="col mt-2">
        <label for="status_id{{ $sufixo }}" class="form-label">Status*</label>
        <select name="status_id" id="status_id{{ $sufixo }}" class="form-select" tabindex="-1">
            <option value="0">Selecione</option>
        </select>
    </div>
</div>

<div class="campos-personalizados"></div>

<div class="row">
    <div class="col mt-2">
        <label for="observacao{{ $sufixo }}" class="form-label">Observação</label>
        <input type="text" id="observacao{{ $sufixo }}" name="observacao" class="form-control">
    </div>
</div>

<div class="row div-resetar-lancamentos" style="display: none;">
    <div class="col mt-2">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="resetar_pagamento_bln{{ $sufixo }}"
                name="resetar_pagamento_bln" disabled>
            <label class="form-check-label" for="resetar_pagamento_bln{{ $sufixo }}"
                title="Esta opção exclui os lançamentos existentes e os recria com as informações atualizadas. A ação só será executada se nenhum dos lançamentos atuais, em momento algum, movimentaram alguma conta.">
                Recriar lançamentos
                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                    data-bs-title="Esta opção exclui os lançamentos existentes e os recria com as informações atualizadas. A ação só será executada se nenhum dos lançamentos atuais, em momento algum, movimentaram alguma conta."></i>
            </label>
        </div>
    </div>
    {{-- <div class="form-text">
        Esta opção exclui os lançamentos existentes e os recria com as informações atualizadas. A ação
        só será executada se nenhum dos lançamentos atuais, em momento algum, movimentaram alguma conta.
    </div> --}}
</div>

<div class="row div-liquidado-migracao" style="display: none;">
    <div class="col mt-2">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch"
                id="liquidado_migracao_bln{{ $sufixo }}" name="liquidado_migracao_bln" disabled>
            <label class="form-check-label" for="liquidado_migracao_bln{{ $sufixo }}"
                title="Aplica o status 'Liquidado (Migração Sistema)' a lançamentos anteriores ao mês atual, sem movimentar valores.">
                Marcar como Liquidado (Migração Sistema)
                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true"
                    data-bs-title="Ao ativar, lançamentos anteriores ao mês atual serão marcados como <span class='fst-italic fw-bolder'>Liquidado (Migração Sistema)</span>, apenas para fins históricos, sem movimentar valores."></i>
            </label>
        </div>
        {{-- <div class="form-text">
            Ao ativar, lançamentos anteriores ao mês atual serão marcados como <span class="fst-italic">Liquidado
                (Migração Sistema)</span>,
            apenas para fins históricos, sem movimentar valores.
        </div> --}}
    </div>
</div>

{{-- 
<div class="row">
    <div class="col-12 mt-2">
        <label for="descricao_condicionado{{ $sufixo }}" class="form-label">Descrição condicionado</label>
        <textarea name="descricao_condicionado" id="descricao_condicionado{{ $sufixo }}" class="form-control"></textarea>
    </div>
</div>
--}}
