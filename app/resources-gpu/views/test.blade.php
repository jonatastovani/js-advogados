@php
    use App\Services\Cache\CacheManager;
@endphp
@extends('layouts.layout-guest')
@section('title', 'Test')

@section('conteudo')
    <div class="row h-100 justify-content-center align-items-center">
        <div class="col-12 text-center">
            @php
                $cache = new CacheManager();
                $recuperado = $cache->exists('views');
                $cache->increment('views');
                $views = $cache->get('views');
            @endphp
            @dump($views)
            <h1 class="display-6">'{{ $views}}' Contador Redis</h1>
            <h1 class="display-6">'{{ $recuperado}}' Se existe a view</h1>
            <h1 class="display-6">{{ now() }}</h1>
        </div>
    </div>
@endsection
