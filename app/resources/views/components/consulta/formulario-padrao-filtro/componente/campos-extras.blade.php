<div class="row mt-2">
    @foreach ($dados->camposExtras as $campo)
        <div class="{{ $col_personalizacao }}">

            @if (isset($campo['label']))
                <label for="{{ $campo['nome'] . $sufixo }}" class="form-label">{{ $campo['label'] }}</label>
            @endif

            <div class="{{ isset($campo['input_group']) ? 'input-group mt-2' : '' }}">
                {{-- Renderizar múltiplos elementos em 'before' --}}
                @if (isset($campo['input_group']['before']))
                    @foreach ($campo['input_group']['before'] as $beforeElement)
                        {!! $beforeElement !!}
                    @endforeach
                @endif

                {{-- Renderizar o campo principal --}}
                @if ($campo['tipo'] === 'select')
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
            </div>

        </div>
    @endforeach
</div>
