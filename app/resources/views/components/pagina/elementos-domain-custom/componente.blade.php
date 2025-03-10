@php
    $domains = \App\Helpers\TenantTypeDomainCustomHelper::getDomainsPorUsuario();
    $domainCustomBln = \App\Helpers\TenantTypeDomainCustomHelper::getDomainCustomBln();
@endphp

{{-- Se for identificação manual do domínio, então se insere a opção de selecionar o domínio --}}
@if ($domainCustomBln)

    {{-- Inicia sempre oculto e o script verifica se mostra ou não --}}
    <div class="{{-- d-inline-flex --}} {{ $domainCustomIdentificationClassName }}" style="display: none;">
        <div class="input-group">
            <div class="input-group-text">
                <label for="domain_id{{ $sufixo }}">Unidade*</label>
            </div>
            <select name="domain_id" id="domain_id{{ $sufixo }}" class="form-select">
                @foreach ($domains as $domain)
                    <option value="{{ $domain->id }}">{{ $domain->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

@endif
