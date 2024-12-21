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
                <p class="fst-italic card-title">Insira suas credenciais para login</p>

                @php
                    $erro_login = new Illuminate\Support\Fluent(Session::get('error_login') ?? []);
                    $username = $erro_login->username ?? '';
                    $password = $erro_login->password ?? '';
                @endphp

                <form id="form_login" method="POST" enctype="multipart/form-data" action="{{ route('login.post') }}">
                    @csrf
                    <div class="form-floating mt-3 mb-3">
                        <input type="text" id="username" name="username" class="form-control"
                            placeholder="Digite seu nome de usuário" aria-label="Nome de Usuário"
                            aria-describedby="label-username" value="{{ $username }}" required autofocus>
                        <label for="username" id="label-username">Usuário</label>
                    </div>
                    <div class="input-group mt-3 mb-3">
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Senha"
                                aria-label="Senha" aria-describedby="label-password" value="{{ $password }}" required>
                            <label for="password" id="label-password">Senha</label>
                        </div>
                        <button class="btn btn-outline-secondary" type="button" id="show-password"
                            title="Mostrar/Ocultar senha">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>

                    <div class="row error_login">
                        @if ($erro_login->error)
                            {{-- @dump($erro_login) --}}
                            <div class="alert alert-danger alert-dismissible mb-0 fade show" role="alert">
                                <strong class="py-1">Houve um problema ao realizar o login</strong>
                                <hr>
                                {{ $erro_login->error['message'] }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                    </div>

                    {{-- @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            {{ $error }} <br>
                        @endforeach
                    @endif --}}

                    <div class="row mt-3 mb-3">
                        <div class="col-12 text-center">
                            <button id="send" type="submit" class="btn btn-outline-primary w-50">Entrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    @vite('resources/js/views/auth/login.js')
@endpush
