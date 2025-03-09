@php
    $nameAttributeKey = config('tenancy_custom.tenant_type.name_attribute_key');
    $headerAttributeKey = config('tenancy_custom.tenant_type.header_attribute_key');
    $domains = \App\Helpers\TenantTypeDomainCustomHelper::getDomainsPorUsuario();
@endphp

{{-- A variável $domainCustomIdentificationClassName está iniciada no boot do providers --}}

<div class="row mx-0">
    <div class="input-group input-group-sm">
        <div class="input-group-text border-0"><label for="{{ $nameAttributeKey }}">Unidade: </label></div>
        <select class="form-select form-select-sm" name="{{ $nameAttributeKey }}" id="{{ $nameAttributeKey }}">
            <option value="0">Todas as unidades</option>
            @foreach ($domains as $domain)
                <option value="{{ $domain->id }}">{{ $domain->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<script type="module">
    window.domainCustom = {
        nameAttributeKey: '{{ $nameAttributeKey }}',
        headerAttributeKey: '{{ $headerAttributeKey }}',
        arrayDomains: @json($domains),
        domainCustomIdentificationClassName: '{{ $domainCustomIdentificationClassName }}',
    };
</script>
