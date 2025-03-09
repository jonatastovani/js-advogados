@php
    use App\Helpers\TenantTypeDomainCustomHelper;
    $domainName = TenantTypeDomainCustomHelper::getDomainNameSelected();
@endphp

<figure>
    <blockquote class="blockquote">
        <p class="name-domain-custom" data-base-value="{{ $paginaDados->nome }}">
            {{ $paginaDados->nome }}
            {{-- <span class="current-domain-name">
                @if ($domainName != '')
                    • {{ $domainName }}
                @endif
            </span> --}}
        </p>
    </blockquote>
    @if (!empty($paginaDados->descricao))
        @foreach ($paginaDados->descricao as $descricao)
            @php
                $class_add = $descricao['class_add'] ?? '';
            @endphp
            <figcaption class="blockquote-footer {{ $class_add }}">
                {{ $descricao['texto'] }}
            </figcaption>
        @endforeach
    @endif
</figure>
