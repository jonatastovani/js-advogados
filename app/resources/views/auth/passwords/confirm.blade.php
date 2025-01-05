@php
    // $sufixo = 'PageMovimentacaoContaIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Confirmar Senha',
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
                                <h3 class="ms-2 mb-0 d-inline-flex">{{ __('Confirm Password') }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Mensagem Informativa -->
                    <p class="text-center text-muted">
                        {{ __('Please confirm your password before continuing.') }}
                    </p>

                    <!-- Formulário de Confirmação -->
                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        <!-- Password Field -->
                        <div class="form-floating mb-3">
                            <input id="password" type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   placeholder="Senha">
                            <label for="password">{{ __('Password') }}</label>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid d-sm-block text-end">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Confirm Password') }}
                            </button>
                        </div>

                        <!-- Forgot Password Link -->
                        <div class="text-end mt-3">
                            @if (Route::has('password.request'))
                                <a class="btn btn-link p-0" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
