@php
    // $sufixo = 'PageMovimentacaoContaIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Verificação de Email',
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
                                <h3 class="ms-2 mb-0 d-inline-flex">{{ __('Verify Your Email Address') }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Mensagem de Confirmação -->
                    @if (session('resent'))
                        <div class="alert alert-success text-center" role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif

                    <!-- Instruções -->
                    <p class="text-center text-muted mb-4">
                        {{ __('Before proceeding, please check your email for a verification link.') }}
                        <br>
                        {{ __('If you did not receive the email') }},
                    </p>

                    <!-- Botão para Reenviar -->
                    <form class="text-center" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link text-primary">
                            {{ __('click here to request another') }}
                        </button>
                    </form>
                    <p class="form-text text-end fw-bolder mb-0 my-1 mx-3">By {{ config('sistema.nome') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
