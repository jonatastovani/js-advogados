@php
    // $sufixo = 'PageMovimentacaoContaIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Registre-se',
    ]);
    Session::put('paginaDados', $paginaDados);
@endphp

@extends('layouts.layout-guest')

@section('conteudo')
    <div class="row justify-content-center align-items-center h-100">
        <div class="col-md-8 col-lg-5" style="max-width: 30rem;">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <!-- Logo e Título -->
                    <div class="row mb-3">
                        <div class="col mt-3">
                            <div class="d-flex align-items-center justify-content-center">
                                <img src="{{ asset(config('sistema.logo')) }}" alt="Logo {{ config('sistema.sigla_front') }}"
                                    width="27">
                                <h3 class="ms-2 mb-0 d-inline-flex">{{ __('Register') }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Formulário de Registro -->
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Name Field -->
                        <div class="form-floating mb-3">
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ old('name') }}" required autocomplete="name" autofocus
                                placeholder="Nome">
                            <label for="name">{{ __('Name') }}</label>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Email Field -->
                        <div class="form-floating mb-3">
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" required autocomplete="email"
                                placeholder="E-mail">
                            <label for="email">{{ __('Email Address') }}</label>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="form-floating mb-3">
                            <input id="password" type="password"
                                class="form-control @error('password') is-invalid @enderror" name="password" required
                                autocomplete="new-password" placeholder="Senha">
                            <label for="password">{{ __('Password') }}</label>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="form-floating mb-3">
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation"
                                required autocomplete="new-password" placeholder="Confirme sua senha">
                            <label for="password-confirm">{{ __('Confirm Password') }}</label>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid d-sm-block text-end">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Register') }}
                            </button>
                        </div>
                    </form>
                    <p class="form-text text-end fw-bolder my-1 mx-3">By {{ config('sistema.nome') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection