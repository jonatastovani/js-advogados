@php
    use App\Helpers\TenantTypeDomainCustomHelper;
    $domainName = TenantTypeDomainCustomHelper::getDomainNameSelected();
    $domainCustomBln = TenantTypeDomainCustomHelper::getDomainCustomBln();
@endphp

<nav class="navbar navbar-top fixed-top bg-body-tertiary border-bottom border-opacity-50">
    <div class="collapse navbar-collapse justify-content-between d-flex">
        <div class="navbar-logo">
            <button class="navbar-toggler me-2 d-lg-none btn-sm" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon" style="max-width: 1.7rem"></span>
            </button>
            <a class="navbar-brand me-1 me-sm-3 " href="{{ route('home') }}">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center">
                        <img src="{{ asset(config('sistema.logo')) }}"
                            alt="Logo {{ Session::get('tenantDados')->name }}" width="27">
                        <h5 class="logo-text ms-2 d-none d-sm-block">
                            {{ tenant('name') }}
                            <span class="current-domain-name">
                                @if ($domainName != '' && !$domainCustomBln)
                                    • {{ $domainName }}
                                @endif
                            </span>
                    </div>
                </div>
            </a>
        </div>

        @component('components.nav.nav-top.menu-usuario')
        @endcomponent

    </div>
</nav>

{{-- 
@push('scripts')
    <script type="module">
        const array = $('.scrollbar');
        for (let index = 0; index < array.length; index++) {
            new SimpleBar(array[index]);
        }
    </script>
@endpush --}}
