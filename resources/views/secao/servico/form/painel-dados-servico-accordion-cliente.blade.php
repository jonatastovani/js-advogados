<div class="accordion mt-2" id="accordionCliente{{ $sufixo }}">
    <div class="accordion-item">
        <div class="accordion-header">
            <button class="accordion-button py-1  {{-- collapsed --}}" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseOne{{ $sufixo }}" aria-expanded="true"
                aria-controls="collapseOne{{ $sufixo }}">
                Cliente(s) relacionado(s)
            </button>
        </div>
        <div id="collapseOne{{ $sufixo }}" class="accordion-collapse {{-- collapse --}}"
            data-bs-parent="#accordionCliente{{ $sufixo }}">
            <div class="accordion-body">
                <div class="row">
                    <div class="col text-end">
                        <button type="button" class="btn btn-outline-primary" id="btnAdicionarClientes">Adicionar
                            Cliente</button>
                    </div>
                </div>
                <div id="divClientes{{ $sufixo }}" class="row row-cols-1 row-cols-md-2 row-cols-xxl-3 g-4 mt-2">
                </div>
            </div>
        </div>
    </div>
</div>
