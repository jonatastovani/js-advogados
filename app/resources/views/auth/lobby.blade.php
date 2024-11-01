@extends('layouts.layout-guest')
@section('title', 'Login')

@section('conteudo')

    <div class="row justify-content-center align-items-center h-100">
        <div class="card shadow-lg" style="max-width: 30rem;">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col mt-3">
                        {{-- <h2 class="text-center fw-bolder">{{ config('sistema.nome') }}</h2> --}}
                        <div class="d-flex align-items-center justify-content-center">
                            <img src="{{ asset(config('sistema.logo')) }}" alt="Logo {{ config('sistema.sigla_front') }}"
                                width="27">
                            <h2 class="ms-2 d-inline-flex">{{ config('sistema.nome') }}</h2>
                        </div>
                    </div>
                </div>
                <p class="fst-italic card-title">Unidades/Módulos Liberados para o seu Usuário</p>
                {{ dump(Auth::user()) }}
                @isset($dadosUsuario)
                    @dump($dadosUsuario)
                @else
                    <p>Não tem dados do usuário</p>
                @endisset ($dadosUsuario)

            </div>
        </div>
    </div>

@endsection

@push('scripts')
    @vite('resources/js/views/auth/login.js')
@endpush
