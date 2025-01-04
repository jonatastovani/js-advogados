<div class="row h-100">
    <form id="form{{ $sufixo }}">
        <div class="row h-100">
            <div class="col d-flex flex-column">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-1 row-cols-xl-2 align-items-end">
                    <div class="col mt-2">
                        <label for="name{{ $sufixo }}" class="form-label">Nome da Empresa*</label>
                        <input type="text" id="name{{ $sufixo }}" name="name" class="form-control">
                        <div class="form-text">
                            Nome exibido no sistema
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
