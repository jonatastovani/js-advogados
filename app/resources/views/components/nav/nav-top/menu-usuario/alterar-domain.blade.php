@php
    $nameAttributeKey = config('tenancy_custom.tenant_type.name_attribute_key');
    $headerAttributeKey = config('tenancy_custom.tenant_type.header_attribute_key');
@endphp
<div class="row mx-2">
    <select class="form-select form-select-sm" name="{{ $nameAttributeKey }}" id="{{ $nameAttributeKey }}">
        @for ($i = 0; $i < 60; $i++)
            <option value="{{ $i }}">Todas as unidades {{ $i }}</option>
        @endfor
    </select>
</div>

<script type="module">
    window.domainCustom = {
        nameAttributeKey: '{{ $nameAttributeKey }}',
        headerAttributeKey: '{{ $headerAttributeKey }}',
    };
</script>
