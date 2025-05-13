@extends('layouts.layout-guest')
@section('title', 'Sem Unidade de Acesso')

@section('conteudo')
    <div class="container py-5 text-center">
        <div class="alert alert-warning" role="alert">
            <h1 class="display-5"><i class="bi bi-exclamation-triangle"></i> Acesso não disponível</h1>
            <p class="lead">Você ainda não possui unidades de acesso cadastradas.</p>
            <p>Por favor, entre em contato com o administrador do sistema para obter acesso.</p>
        </div>
        <div class="mt-4">
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="btn btn-danger">
                Realizar Logout
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
@endsection
