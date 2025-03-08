@php
    use Carbon\Carbon;
@endphp
<div class="row">
    <div class="col mt-2">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="ativo_bln{{ $sufixo }}" name="ativo_bln"
                checked>
            <label class="form-check-label" for="ativo_bln{{ $sufixo }}">Agendamento ativo</label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="recorrente_bln"
                id="ckbRecorrente{{ $sufixo }}">
            <label class="form-check-label" for="ckbRecorrente{{ $sufixo }}">Agendamento recorrente</label>
        </div>
    </div>
</div>

<div class="fs-5 mt-2">Selecione o intervalo de recorrência</div>
<div class="row row-cols-1 dadosCron">

    <div class="col mt-2">
        <div class="input-group">
            <div class="input-group-text">
                <label for="cronDay{{ $sufixo }}">Todo dia</label>
            </div>
            <select class="form-select inputCron" id="cronDay{{ $sufixo }}" name="cronDay">
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
            <select class="form-select inputCron" id="cronMonth{{ $sufixo }}" name="cronMonth">
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
            <select class="form-select inputCron" id="cronWeekday{{ $sufixo }}" name="cronWeekday">
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

    {{-- <div class="col mt-2">
        <div class="input-group">
            <div class="input-group-text">
                <label for="cronMinute">Todo minuto</label>
            </div>
            <select class="form-select inputCron" id="cronMinute{{ $sufixo }}" name="cronMinute">
                <option value="*">Qualquer minuto</option>
                @for ($i = 0, $count = 59; $i < $count; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>

    <div class="col mt-2">
        <div class="input-group">
            <div class="input-group-text">
                <label for="cronHour">Toda hora</label>
            </div>
            <select class="form-select inputCron" id="cronHour{{ $sufixo }}" name="cronHour">
                <option value="*">Qualquer hora</option>
                @for ($i = 0, $count = 23; $i < $count; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div> --}}
</div>

<div class="row row-cols-1 dadosCron">
    <div class="col mt-2">
        <label for="cronExpression{{ $sufixo }}">Verifique a recorrência gerada</label>
        <input type="text" id="cronExpression{{ $sufixo }}" class="form-control mt-2 inputCron" readonly>
    </div>
</div>

<div class="row row-cols-1 row-cols-sm-2 dadosCron">
    <div class="col mt-2">
        <label for="cron_data_inicio{{ $sufixo }}" class="form-label">Data Início*</label>
        <input type="date" id="cron_data_inicio{{ $sufixo }}" name="cron_data_inicio"
            class="form-control text-center inputCron">
    </div>
    <div class="col mt-2">
        <label for="cron_data_fim{{ $sufixo }}" class="form-label">Data Final (opcional)</label>
        <input type="date" id="cron_data_fim{{ $sufixo }}" name="cron_data_fim"
            class="form-control text-center inputCron">
    </div>
</div>
<div class="form-text">
    Este intervalo de datas é o período que o agendamento será vigente e executado. Por padrão, o agendamento será
    inserido na listagem dos <i>Lançamentos Gerais</i> 30 dias antes.
</div>
