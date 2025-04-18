<div class="d-flex flex-column h-100">

    <div class="row div-personalizar-lancamentos" style="display: none;">
        <div class="col mt-2">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch"
                    id="personalizar_lancamentos_bln{{ $sufixo }}" name="personalizar_lancamentos_bln" disabled>
                <label class="form-check-label" for="personalizar_lancamentos_bln{{ $sufixo }}">
                    Personalizar lançamentos
                    <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-title="Ao ativar esta opção, os lançamentos poderão ser personalizados individualmente. Para tipos de pagamento que exigem um valor total, a soma dos lançamentos personalizados deverá corresponder exatamente ao valor total informado."></i>
                </label>
            </div>
            {{-- <div class="form-text">
                Ao ativar esta opção, os lançamentos poderão ser personalizados individualmente.
                Para tipos de pagamento que exigem um valor total, a soma dos lançamentos personalizados deverá
                corresponder exatamente ao valor total informado.
            </div> --}}
        </div>
    </div>

    <div class="row flex-fill flex-column row-lancamentos g-2 mt-2"></div>

</div>
