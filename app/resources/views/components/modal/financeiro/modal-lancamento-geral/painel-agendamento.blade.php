@php
    use Carbon\Carbon;
@endphp
<div class="row">
    <div class="col mt-2">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="ativo_bln{{ $sufixo }}" name="ativo_bln" checked>
            <label class="form-check-label" for="ativo_bln{{ $sufixo }}">Agendamento ativo</label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="recorrente{{ $sufixo }}">
            <label class="form-check-label" for="recorrente{{ $sufixo }}">Incluir um agendamento recorrente</label>
        </div>
    </div>
</div>

<div class="fs-5 mt-2">Selecione o intervalo de repetição</div>
<div class="row row-cols-1" id="dadosRecorrente">
    <div class="col mt-2">
        <div class="input-group">
            <div class="input-group-text">
                <label for="cronDay">Todo dia</label>
            </div>
            <select class="form-select" id="cronDay" name="cronDay">
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
                <label for="cronMonth">Todo mês</label>
            </div>
            <select class="form-select" id="cronMonth" name="cronMonth">
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
                <label for="cronWeekday">Todo dia da semana</label>
            </div>
            <select id="cronWeekday" class="form-select" name="cronWeekday">
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

    <div class="col mt-2">
        <div class="input-group">
            <div class="input-group-text">
                <label for="cronMinute">Todo minuto</label>
            </div>
            <select id="cronMinute" class="form-select" name="cronMinute">
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
            <select id="cronHour" class="form-select" name="cronHour">
                <option value="*">Qualquer hora</option>
                @for ($i = 0, $count = 23; $i < $count; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>
</div>

<div class="row row-cols-1">
    <div class="col mt-2"><span>Verifique a recorrência gerada</span></div>
    <div class="col mt-2">
        <input type="text" id="cronExpression" class="form-control mt-2" readonly
            placeholder="Recorrência gerada">
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2">
    <div class="col mt-2">
        <label for="cron_data_inicio{{ $sufixo }}" class="form-label">Data Início</label>
        <input type="date" id="cron_data_inicio{{ $sufixo }}" name="cron_data_inicio"
            class="form-control text-center">
    </div>
    <div class="col mt-2">
        <label for="cron_data_fim{{ $sufixo }}" class="form-label">Data Final</label>
        <input type="date" id="cron_data_fim{{ $sufixo }}" name="cron_data_fim"
            class="form-control text-center">
    </div>
</div>
<div class="form-text">
    Este intervalo de datas é o período que o agendamento será vigente e executado. Por padrão, o agendamento será
    inserido na listagem dos <i>Lançamentos Gerais</i> um mês antes.
</div>
