@php
    use Carbon\Carbon;

    if (!isset($sufixo)) {
        $sufixo = 'ModalServicoPagamento';
    }
    if (isset($requestData)) {
        $readonly = $requestData->modo_editar_bln ? 'readonly' : '';
    }
@endphp


<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 dadosCron">
    <div class="col mt-2">
        <label for="parcela_valor{{ $sufixo }}" class="form-label">Valor da parcela</label>
        <div class="input-group">
            <div class="input-group-text"><label for="parcela_valor{{ $sufixo }}">R$</label></div>
            <input type="text" id="parcela_valor{{ $sufixo }}" name="parcela_valor"
                class="form-control text-end campo-monetario" {{ $readonly }}>
        </div>
    </div>

    <div class="col mt-2">
        <label for="cron_data_inicio{{ $sufixo }}" class="form-label">Data Início</label>
        <input type="date" id="cron_data_inicio{{ $sufixo }}" name="cron_data_inicio"
            class="form-control text-center inputCron" {{ $readonly }}>
    </div>

    {{-- <div class="col mt-2">
        <label for="cron_data_fim{{ $sufixo }}" class="form-label">Data Final (opcional)</label>
        <input type="date" id="cron_data_fim{{ $sufixo }}" name="cron_data_fim"
            class="form-control text-center inputCron">
    </div> --}}
</div>

<div class="form-text">
    Este intervalo de datas é o período que o lançamento recorrente será vigente e executado. Por padrão, o lançamento será inserido na listagem dos <i>Lançamentos de Serviços</i> 30 dias antes.
</div>

<div class="fs-5 mt-2">Selecione o intervalo de recorrência</div>
<div class="row row-cols-1 row-cols-lg-2 dadosCron">

    <div class="col mt-2">
        <div class="input-group">
            <div class="input-group-text">
                <label for="cronDay{{ $sufixo }}">Todo dia</label>
            </div>
            <select class="form-select inputCron" id="cronDay{{ $sufixo }}" name="cronDay" {{ $readonly ? 'disabled' : '' }}>
                <option value="*">Qualquer dia</option>
                @for ($i = 1, $count = 32; $i < $count; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>

    <div class="col mt-2">
        <div class="input-group">
            <div class="input-group-text">
                <label for="cronMonth{{ $sufixo }}">Todo mês</label>
            </div>
            <select class="form-select inputCron" id="cronMonth{{ $sufixo }}" name="cronMonth" {{ $readonly ? 'disabled' : '' }}>
                <option value="*">Qualquer mês</option>
                @for ($i = 1, $count = 13; $i < $count; $i++)
                    <option value="{{ $i }}">
                        {{ Carbon::createFromDate(null, $i, 1)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
        </div>
    </div>
    <div class="col mt-2">
        <div class="input-group">
            <div class="input-group-text">
                <label for="cronWeekday{{ $sufixo }}">Todo dia da semana</label>
            </div>
            <select class="form-select inputCron" id="cronWeekday{{ $sufixo }}" name="cronWeekday" {{ $readonly ? 'disabled' : '' }}>
                <option value="*">Qualquer dia</option>
                <option value="1">Segunda-feira</option>
                <option value="2">Terça-feira</option>
                <option value="3">Quarta-feira</option>
                <option value="4">Quinta-feira</option>
                <option value="5">Sexta-feira</option>
                <option value="6">Sábado</option>
                <option value="0">Domingo</option>
            </select>
        </div>
    </div>
</div>

<div class="row row-cols-1 dadosCron">
    <div class="col mt-2">
        <label for="cronExpression{{ $sufixo }}">Verifique a recorrência gerada</label>
        <input type="text" id="cronExpression{{ $sufixo }}" class="form-control mt-2 inputCron" readonly>
    </div>
</div>
