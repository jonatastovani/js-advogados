@php

    use App\Enums\PdfMarginPresetsEnum;

    $margins = $dataEnv['margins'] ?? PdfMarginPresetsEnum::NORMAL->detalhes();
@endphp

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'PDF Document')</title>
    <link rel="shortcut icon" href="{{ asset('img/logo-js-sem-fundo-1600x1600-ico.ico') }}" type="image/x-icon">

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
            top: 0.5cm;
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
        <div class="row">
            <div class="col-sm-2 text-right mt-2">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo-js-sem-fundo-1600x1600.png'))) }}"
                    alt="Logo" height="100%">
            </div>
            <div class="col-sm-7 text-center">
                <h4 class="mb-0">JS Advogados</h2>
            </div>
            {{-- <div class="col-sm-2 text-left mt-2">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo-js-20-anos-sem-fundo-1870x1870.png'))) }}"
                    alt="Logo" height="100%">
            </div> --}}
        </div>
    </header>

    <footer>
        @yield('footer')
        {{-- <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo-js-adv.jpg'))) }}"
            alt="Logo" height="100%"> --}}
        <div class="col-12 text-center">
            <h5 class="mb-0">JORGE SILVA SOCIEDADE INDIVIDUAL DE ADVOCACIA</h5>
            <p class="mb-0">Av. Monte Castelo, 759 - Centro, Santa Bárbara d'Oeste - SP, 13450-031, Brasil</p>
            <p class="mb-0"><span class="me-1">juridico@jsassociados.com.br</span><span class="ms-1">CNPJ
                    40.910.109/0001-80</span></p>
        </div>
    </footer>

    <!-- Wrap the content of your PDF inside a main tag -->
    <main>
        @yield('content')
    </main>
</body>

</html>
