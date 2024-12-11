<div class="row mt-2">
    @foreach ($dados->camposExtras as $campo)
        <div class="{{ $col_personalizacao }}">

            @if (isset($campo['label']))
                <label for="{{ $campo['nome'] . $sufixo }}" class="form-label">{{ $campo['label'] }}</label>
            @endif

            {{-- Insere div para input group --}}
            @if (isset($campo['input_group']))
                <div class="input-group mt-2">
                @else
                    <div class="mt-2">
            @endif

            {{-- Renderizar múltiplos elementos em 'before' --}}
            @if (isset($campo['input_group']['before']))
                @foreach ($campo['input_group']['before'] as $beforeElement)
                    {!! $beforeElement !!}
                @endforeach
            @endif

            {{-- Renderizar o campo principal --}}
            @if ($campo['tipo'] === 'radio')
                {{-- Se não tiver label, renderizar os radios alinhados no centro --}}
                @if (!isset($campo['label']))
                    <div class="row h-100 align-items-center">
                @endif
                @foreach ($campo['opcoes'] as $opcao)
                    <div class="col">
                        <div class="form-check" title="{{ $opcao['title'] }}">
                            <input type="radio" class="form-check-input" id="{{ $opcao['id'] }}"
                                name="{{ $campo['nome'] }}" value="{{ $opcao['valor'] }}"
                                {{ isset($opcao['checked']) && $opcao['checked'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $opcao['id'] }}">{{ $opcao['label'] }}</label>
                        </div>
                    </div>
                @endforeach
                @if (!isset($campo['label']))
                    @php
                        echo '</div>';
                    @endphp
                @endif
            @elseif ($campo['tipo'] === 'select')
                <select name="{{ $campo['nome'] }}" id="{{ $campo['nome'] . $sufixo }}" class="form-select">
                    @foreach ($campo['opcoes'] as $opcao)
                        <option value="{{ $opcao['id'] }}" {{ isset($opcao['selecionado']) ? 'selected' : '' }}>
                            {{ $opcao['nome'] }}
                        </option>
                    @endforeach
                </select>
            @else
                <input type="{{ $campo['tipo'] }}" name="{{ $campo['nome'] }}" id="{{ $campo['nome'] . $sufixo }}"
                    class="form-control" value="{{ $campo['valor'] ?? '' }}">
            @endif

            {{-- Renderizar múltiplos elementos em 'after' --}}
            @if (isset($campo['input_group']['after']))
                @foreach ($campo['input_group']['after'] as $afterElement)
                    {!! $afterElement !!}
                @endforeach
            @endif

            {{-- Insere fechamento div --}}
            @php
                echo '</div>';
            @endphp
        </div>
    @endforeach
</div>
