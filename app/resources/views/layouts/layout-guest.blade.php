@php
    use Stancl\Tenancy\Resolvers\DomainTenantResolver;
@endphp

<!doctype html>
<html lang="pt-BR" data-bs-theme="auto">

<head>

    <script src="{{ asset('js/site/color-modes.js') }}"></script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ tenant('sigla') }} • {{ DomainTenantResolver::$currentDomain->name }} - @yield('title')</title>
    <link rel="icon" href="{{ asset(config('sistema.logo')) }}">
    @vite(['resources/sass/app.scss', 'resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <div class="container vh-100">
        @yield('conteudo')
    </div>

    @stack('scripts')
</body>

</html>
