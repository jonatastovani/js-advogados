@php
    use App\Helpers\TenantTypeDomainCustomHelper;
    $domainName = TenantTypeDomainCustomHelper::getDomainNameSelected();
    $domainCustomBln = TenantTypeDomainCustomHelper::getDomainCustomBln();
@endphp

<!doctype html>
<html lang="pt-BR" data-bs-theme="auto">

<head>
    <script src="{{ asset('js/site/color-modes.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title id="page-title" data-base-title="{{ tenant('sigla') }}" data-yield-title="@yield('title')">
        {{ tenant('sigla') }}
        @if ($domainName && !$domainCustomBln)
            • {{ $domainName }}
        @endif
        - @yield('title')
    </title>
    <link rel="icon" href="{{ asset(config('sistema.logo')) }}">
    @vite(['resources/sass/app.scss', 'resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    {{ Session::put('tenantDados', tenant()) }}

    {{-- @component('components.toggleMode')
    @endcomponent --}}

    @component('components.nav.nav-vertical')
    @endcomponent

    @component('components.nav.nav-top')
    @endcomponent

    @php
        $sufixo =
            Session::get('paginaDados') && Session::get('paginaDados')->sufixo
                ? Session::get('paginaDados')->sufixo
                : 'PageContent';
    @endphp

    <div id="{{ $sufixo }}" class="content d-flex flex-column">
        @yield('conteudo')
    </div>

    @include('components.pagina.injetar-js')

    @stack('modals')

    <x-modal.comum.modal-loading.modal />
    <x-modal.comum.modal-message.modal />

    @vite('resources/js/views/DefaultScriptLayoutBefore.js')

    @stack('scripts')

    @vite('resources/js/views/DefaultScriptLayoutAfter.js')
</body>

</html>
