@php
    use App\Helpers\TenantTypeDomainCustomHelper;
    use Illuminate\Support\Fluent;

    // Obtém os domínios disponíveis e a configuração do domínio customizado
    $domains = TenantTypeDomainCustomHelper::getDomainsPorUsuario();
    $domainCustomBln = TenantTypeDomainCustomHelper::getDomainCustomBln();

    // Configuração do display padrão
    $display = isset($display) ? $display : false;

    // Inicializa `$dados` como um Fluent para facilitar a manipulação de dados opcionais
    $dados = isset($dados) ? $dados : new Fluent();

    // Configuração das classes personalizáveis
    $divCapsula = new Fluent([
        'appendClass' => '',
    ]);
    $divInputGroup = new Fluent([
        'appendClass' => '',
    ]);
    $divInputGroupText = new Fluent([
        'appendClass' => '',
    ]);
    $select = new Fluent([
        'appendClass' => '',
    ]);

    // Se `$dados['domainCustomComponent']` existir, aplica as personalizações
    if (isset($dados->domainCustomComponent)) {
        $divCapsula = new Fluent($dados->domainCustomComponent['divCapsula'] ?? $divCapsula);
        $divInputGroup = new Fluent($dados->domainCustomComponent['divInputGroup'] ?? $divInputGroup);
        $divInputGroupText = new Fluent($dados->domainCustomComponent['divInputGroupText'] ?? $divInputGroupText);
        $select = new Fluent($dados->domainCustomComponent['select'] ?? $select);
    }
@endphp

{{-- Se for identificação manual do domínio, então insere a opção de selecionar o domínio --}}
@if ($domainCustomBln)
    {{-- Inicia sempre oculto caso o display seja false e o script verifica se mostra ou não --}}
    <div class="{{ $display ? 'd-inline-flex' : '' }} {{ $domainCustomIdentificationClassName }} {{ $divCapsula->appendClass }}"
        {{ $display ? '' : 'style=display:none;' }}>

        <div class="input-group {{ $divInputGroup->appendClass }}">
            <div class="input-group-text {{ $divInputGroupText->appendClass }}">
                <label for="domain_id{{ $sufixo }}">Unidade*</label>
            </div>
            <select name="domain_id" id="domain_id{{ $sufixo }}" class="form-select {{ $select->appendClass }}">
                @foreach ($domains as $domain)
                    <option value="{{ $domain->id }}">{{ $domain->name }}</option>
                @endforeach
            </select>
        </div>

    </div>
@endif
