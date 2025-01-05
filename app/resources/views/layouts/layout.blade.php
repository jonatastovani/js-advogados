@php
    use Stancl\Tenancy\Resolvers\DomainTenantResolver;
@endphp

<!doctype html>
<html lang="pt-BR" data-bs-theme="auto">

<head>
    <script src="{{ asset('js/site/color-modes.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ tenant('sigla') }} • {{ DomainTenantResolver::$currentDomain->name }} - @yield('title')</title>
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

    <div class="content d-flex flex-column">
        @yield('conteudo')
    </div>

    @include('components.pagina.injetar-js')

    @stack('modals')

    <x-modal.comum.modal-loading.modal />
    <x-modal.comum.modal-message.modal />

    @stack('scripts')

    @vite('resources/js/views/DefaultScriptLayout.js')
</body>

</html>
