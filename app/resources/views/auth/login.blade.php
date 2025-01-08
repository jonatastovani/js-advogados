@php
    use Stancl\Tenancy\Resolvers\DomainTenantResolver;

    // $sufixo = 'PageMovimentacaoContaIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Login',
    ]);
    Session::put('paginaDados', $paginaDados);
@endphp

@extends('layouts.layout-guest')

@section('conteudo')
    <div class="row justify-content-center align-items-center h-100">
        <div class="col-md-8 col-lg-5" style="max-width: 30rem;">
            <div class="card shadow-lg border-0">
                {{-- <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">{{ __('Login') }}</h4>
                </div> --}}
                <div class="card-body p-4">

                    <div class="row mb-3">
                        <div class="col mt-3">
                            <div class="d-flex align-items-center justify-content-center">
                                <img src="{{ asset(config('sistema.logo')) }}" alt="Logo {{ tenant('name') }}" width="75">
                                <div class="ms-2 text-center">
                                    <h3 class="d-block mb-0">
                                        {{ tenant('name') }}
                                    </h3>
                                    <h5 class="d-block mb-0">
                                        {{ DomainTenantResolver::$currentDomain->name }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
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

                        <!-- Password Field -->
                        <div class="form-floating mb-3">
                            <input id="password" type="password"
                                class="form-control @error('password') is-invalid @enderror" name="password" required
                                autocomplete="current-password" placeholder="Senha">
                            <label for="password">{{ __('Password') }}</label>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                {{ __('Remember Me') }}
                            </label>
                        </div>

                        @error('csrf_error')
                            <div class="alert alert-danger">
                                {{ $message }}
                            </div>
                        @enderror

                        <!-- Submit Button -->
                        <div class="d-grid d-sm-block text-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                {{ __('Login') }}
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
                        <p class="form-text text-end fw-bolder my-1 mx-3">By {{ config('sistema.nome') }}</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
