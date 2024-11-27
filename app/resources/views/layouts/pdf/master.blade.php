@php
    use App\Enums\PdfMarginPresetsEnum;

    if (isset($margins)) {
        $margins = json_decode($margins, true);
    } else {
        $margins = PdfMarginPresetsEnum::ESTREITA->detalhes();
    }
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'PDF Document')</title>
    <link href="{{ public_path('css/pdf-bootstrap-styles.css') }}" rel="stylesheet">
    <style>
        @page {
            margin: {{ $margins['margin_top'] ?? '30' }}mm {{ $margins['margin_right'] ?? '20' }}mm {{ $margins['margin_bottom'] ?? '20' }}mm {{ $margins['margin_left'] ?? '30' }}mm;
        }
    </style>

</head>

<body>
    <header>
        @yield('header')
    </header>
    <main>
        @yield('content')

        {{ $margins['margin_top'] }} <br>
        {{ $margins['margin_right'] }} <br>
        {{ $margins['margin_bottom'] }} <br>
        {{ $margins['margin_left'] }} <br>
    </main>
    <footer>
        @yield('footer')
    </footer>
</body>

</html>
