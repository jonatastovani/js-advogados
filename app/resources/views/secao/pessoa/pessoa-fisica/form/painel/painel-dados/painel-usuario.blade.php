<div class="row">
    <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mt-2">
        <label for="username{{ $sufixo }}" class="form-label">Usuário</label>
        <input type="text" id="username{{ $sufixo }}" name="username" class="form-control">
    </div>
    <div id="divSenha{{ $sufixo }}" class="col-12 col-md-8 col-lg-12 col-xl-8 mt-2" 
        {{ $recurso ? "style=display:none;" : '' }}>
        <div class="row">
            <div class="col-12 col-sm-6">
                <label for="password{{ $sufixo }}" class="form-label">Senha</label>
                <input type="text" id="password{{ $sufixo }}" name="password"
                    class="form-control" {{ $recurso ? 'disabled' : '' }}>
            </div>
            <div class="col-12 col-sm-6">
                <label for="password_confirmation{{ $sufixo }}" class="form-label">Confirmação</label>
                <input type="text" id="password_confirmation{{ $sufixo }}" name="password_confirmation"
                    class="form-control" {{ $recurso ? 'disabled' : '' }}>
            </div>
        </div>
    </div>
</div>
{{-- Se houver o recurso, significa que é uma alteração, então se dá a opção de alterar a senha --}}
@if ($recurso)
    <div class="row" id="rowAlterarSenhaBln{{ $sufixo }}">
        <div class="col mt-2">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" name="alterar_senha_bln"
                    id="alterar_senha_bln{{ $sufixo }}">
                <label class="form-check-label" for="alterar_senha_bln{{ $sufixo }}">Alterar Senha</label>
            </div>
        </div>
    </div>
@endif
