@php
    // $sufixo = 'PageMovimentacaoContaIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Redefinição de Senha',
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
                                <h3 class="ms-2 mb-0 d-inline-flex">{{ __('Reset Password') }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Mensagem de Status -->
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Formulário de Redefinição -->
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <!-- Email Field -->
                        <div class="form-floating mb-3">
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                                placeholder="E-mail">
                            <label for="email">{{ __('Email Address') }}</label>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid d-sm-block text-end">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Send Password Reset Link') }}
                            </button>
                        </div>
                    </form>
                    <p class="form-text text-end fw-bolder mb-0 my-1">By {{ config('sistema.nome') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
