<form id="form{{ $sufixo }}">
    <div class="row row-cols-1 row-cols-md-2 row-cols-xxl-4">
        <div class="col mt-2">
            <label for="nome{{ $sufixo }}" class="form-label">Nome</label>
            <input type="text" id="nome${{ $sufixo }}" name="nome" class="form-control">
        </div>
    </div>
    <div class="row">
        <div class="col mt-2">
            <label for="descricao{{ $sufixo }}" class="form-label">Descrição</label>
            <input type="text" id="descricao${{ $sufixo }}" name="descricao" class="form-control">
        </div>
    </div>
</form>
