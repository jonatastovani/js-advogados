@php
    use App\Helpers\TenantTypeDomainCustomHelper;
    $domainName = TenantTypeDomainCustomHelper::getDomainNameSelected();
    $domainCustomBln = TenantTypeDomainCustomHelper::getDomainCustomBln();
@endphp

<figure class="mb-0">
    <blockquote class="blockquote">
        <p class="name-domain-custom" data-base-value="{{ $paginaDados->nome }}">
            {{ $paginaDados->nome }}
            @if ($domainCustomBln)
                <span class="blocked-changes-domain">
                    {{-- @if ($domainName != '')
                        • {{ $domainName }}
                    @endif --}}
                </span>
            @endif
        </p>
    </blockquote>
    @if (!empty($paginaDados->descricao))
        @foreach ($paginaDados->descricao as $descricao)
            @php
                $class_add = $descricao['class_add'] ?? 'mb-1';
            @endphp
            <figcaption class="blockquote-footer {{ $class_add }}">
                {{ $descricao['texto'] }}
            </figcaption>
        @endforeach
    @endif
</figure>
