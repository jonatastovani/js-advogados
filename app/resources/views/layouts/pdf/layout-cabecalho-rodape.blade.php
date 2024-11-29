@php

    use App\Enums\PdfMarginPresetsEnum;

    $margins = $dataEnv['margins'] ?? PdfMarginPresetsEnum::NORMAL->detalhes();
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'PDF Document')</title>
    <style>
        {!! file_get_contents(public_path('css/pdf-bootstrap-styles.css')) !!}
    </style>
    <style>
        /**
            Set the margins of the page to 0, so the footer and the header
            can be of the full height and width !
         **/
        @page {
            margin: 0cm 0cm;
        }

        /** Define now the real margins of every page in the PDF **/
        body {
            margin-top: {{ $margins['margin_top'] + 0.5 . 'cm' }};
            margin-left: {{ $margins['margin_left'] . 'cm' }};
            margin-right: {{ $margins['margin_right'] . 'cm' }};
            margin-bottom: {{ $margins['margin_bottom'] . 'cm' }};
        }

        
        /** Define the header rules **/
        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 1.5cm;
        }

        /** Define the footer rules **/
        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
        }
    </style>
</head>

<body>
    <!-- Define header and footer blocks before your content -->
    <header>
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo-js-adv.jpg'))) }}"
            alt="Logo" height="100%">
    </header>

    <footer>
        @yield('footer')
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo-js-adv.jpg'))) }}"
            alt="Logo" height="100%">
    </footer>

    <!-- Wrap the content of your PDF inside a main tag -->
    <main>
        @yield('content')
    </main>
</body>

</html>
