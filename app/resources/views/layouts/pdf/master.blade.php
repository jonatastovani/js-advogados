<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'PDF Document')</title>
    <link href="{{ public_path('css/pdf-bootstrap-styles.css') }}" rel="stylesheet">
    <style>
        @php
            // Configurações de margens padrão
            $defaultMargins = [
                'top' => 10,
                'right' => 20,
                'bottom' => 10,
                'left' => 20,
            ];

            // Mescla configurações passadas com as padrões
            $margins = array_merge($defaultMargins, $config['margins'] ?? []);
        @endphp

        @page {
            margin: {{ $margins['top'] }}px {{ $margins['right'] }}px {{ $margins['bottom'] }}px {{ $margins['left'] }}px;
        }

        body {
            font-family: Arial, sans-serif;
        }
    </style>
</head>

<body>
    <header>
        @yield('header')
    </header>
    <main>
        @yield('content')
    </main>
    <footer>
        @yield('footer')
    </footer>
</body>

</html>
