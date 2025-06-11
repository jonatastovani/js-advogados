@php
    /**
     * Renderiza dinamicamente um campo de formulário conforme as configurações do array $campo.
     *
     * @param array  $campo  Configurações do campo (tipo, nome, label, opções, etc.)
     * @param string $sufixo Sufixo adicional para uso no ID dos campos, garantindo unicidade.
     * @return string HTML do campo renderizado.
     */
    function renderInput(array $campo, string $sufixo = ''): string
    {
        $html = '';

        // Define o tipo do campo (default: 'text')
        $tipo = $campo['tipo'] ?? 'text';

        // Define o ID único para o campo
        $id = $campo['nome'] . $sufixo;

        // Se for checkbox com múltiplos valores, adiciona '[]' no name
        $isCheckboxMultiplo = $tipo === 'checkbox' && !empty($campo['multiplo']);
        $name = $isCheckboxMultiplo ? $campo['nome'] . '[]' : $campo['nome'];

        // Cria a div de input group se especificado, ou uma div simples
        $html .= isset($campo['input_group']) ? '<div class="input-group mt-2">' : '<div class="mt-2">';

        // Renderiza os elementos "before" do input group (ícones, textos, botões, etc.)
        if (!empty($campo['input_group']['before'])) {
            foreach ($campo['input_group']['before'] as $beforeElement) {
                $html .= $beforeElement;
            }
        }

        // Tipos com múltiplas opções (radio ou checkbox)
        if (in_array($tipo, ['radio', 'checkbox']) && !empty($campo['opcoes'])) {
            $html .= isset($campo['label']) ? '' : '<div class="row h-100 align-items-center">';

            foreach ($campo['opcoes'] as $opcao) {
                $opcaoId = $opcao['id'] ?? $campo['nome'] . '_' . $opcao['valor'];
                $checked = !empty($opcao['checked']) ? 'checked' : '';
                $label = $opcao['label'] ?? $opcao['valor'];
                $title = $opcao['title'] ?? '';
                $valor = $opcao['valor'] ?? '';

                $html .= <<<HTML
                    <div class="col">
                        <div class="form-check" title="{$title}">
                            <input type="{$tipo}" class="form-check-input"
                                   id="{$opcaoId}" name="{$name}" value="{$valor}" {$checked}>
                            <label class="form-check-label" for="{$opcaoId}">{$label}</label>
                        </div>
                    </div>
                HTML;
            }

            $html .= isset($campo['label']) ? '' : '</div>';

            // Tipo select (dropdown)
        } elseif ($tipo === 'select' && !empty($campo['opcoes'])) {
            $html .= "<select name=\"{$name}\" id=\"{$id}\" class=\"form-select\">";
            foreach ($campo['opcoes'] as $opcao) {
                $selected = !empty($opcao['selecionado']) ? 'selected' : '';
                $html .= "<option value=\"{$opcao['id']}\" {$selected}>{$opcao['nome']}</option>";
            }
            $html .= '</select>';
        }

        // Outros tipos de input (text, number, date, etc.)
        else {
            $valor = $campo['valor'] ?? '';
            $html .= "<input type=\"{$tipo}\" name=\"{$name}\" id=\"{$id}\" class=\"form-control\" value=\"{$valor}\">";
        }

        // Renderiza os elementos "after" do input group (ícones, botões, etc.)
        if (!empty($campo['input_group']['after'])) {
            foreach ($campo['input_group']['after'] as $afterElement) {
                $html .= $afterElement;
            }
        }

        $html .= '</div>'; // Fecha a div de input
        return $html;
    }
@endphp

{{-- Loop pelos campos extras configurados --}}
<div class="row mt-2">
    @foreach ($dados->camposExtras as $campo)
        <div class="{{ $col_personalizacao }}">

            {{-- Renderiza o label acima do campo, se fornecido --}}
            @if (!empty($campo['label']))
                <label for="{{ $campo['nome'] . $sufixo }}" class="form-label">{{ $campo['label'] }}</label>
            @endif

            {{-- Renderiza o campo utilizando a função helper --}}
            {!! renderInput($campo, $sufixo) !!}

        </div>
    @endforeach
</div>
