@extends('layouts.layout-guest')
@section('title', 'Test')

@section('conteudo')
    <div class="row h-100 justify-content-center align-items-center">
        <div class="col-12 text-center">
            <h1 class="display-6">'{{ $views}}' Contador Redis</h1>
            <h1 class="display-6">{{ now() }}</h1>
        </div>
    </div>
@endsection
