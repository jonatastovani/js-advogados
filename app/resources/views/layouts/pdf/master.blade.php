@php

    use App\Enums\PdfMarginPresetsEnum;

    $margins = $dataEnv['margins'] ?? PdfMarginPresetsEnum::NORMAL->detalhes();
    $margins = "{$margins['margin_top']}cm {$margins['margin_right']}cm {$margins['margin_bottom']}cm {$margins['margin_left']}cm";
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'PDF Document')</title>
    {{-- <link href="{{ asset('css/pdf-bootstrap-styles.css') }}" rel="stylesheet"> --}}
    <style>
        @page {
            margin: <?=$margins ?>;
        }
    </style>
    <style>
        {!! file_get_contents(public_path('css/pdf-bootstrap-styles.css')) !!}
    </style>

</head>

<body>
    <header>
        @yield('header')
        {{-- {{ asset('css/pdf-bootstrap-styles.css') }} <br> --}}
        {{-- {{ public_path('css/pdf-bootstrap-styles.css') }} --}}
    </header>
    <main>
        @yield('content')
    </main>
    <footer>
        @yield('footer')
    </footer>
</body>

</html>
