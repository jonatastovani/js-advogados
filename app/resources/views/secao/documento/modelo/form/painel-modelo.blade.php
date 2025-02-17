<div class="row h-100">
    <form id="form{{ $sufixo }}">
        <div class="row h-100">
            <div class="col d-flex flex-column">

                <div class="row flex-fill">
                    <div class="col d-flex flex-column mt-2">
                        <div class="row">
                            <div class="col"><label for="descricao{{ $sufixo }}"
                                    class="form-label">Descrição</label></div>
                        </div>
                        <div class="row flex-fill">
                            <div class="col d-flex flex-column flex-md-row flex-lg-column flex-xl-row">
                                <textarea name="descricao" id="descricao{{ $sufixo }}" class="form-control flex-fill">
                                </textarea>

                                <div class="row flex-fill">
                                    <div class="col d-flex flex-column ms-md-2 ms-lg-0 ms-xl-2">

                                        <div class="d-grid d-sm-block">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary mt-2 mt-md-0 mt-lg-2 mt-xl-0"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                Adicionar Cliente
                                            </button>
                                        </div>

                                        <div class="accordion mt-2 px-0" id="accordionCliente${item.idCard}">
                                            <div class="accordion-item">
                                                <div class="accordion-header">
                                                    <button class="accordion-button py-1 collapsed" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#collapseCliente${item.idCard}"
                                                        aria-expanded="true"
                                                        aria-controls="collapseCliente${item.idCard}">
                                                        <span class="spanClienteNumero">Cliente.1</span>
                                                    </button>
                                                </div>
                                                <div id="collapseCliente${item.idCard}"
                                                    class="accordion-collapse collapse"
                                                    data-bs-parent="#accordionCliente${item.idCard}">
                                                    <div class="accordion-body">
                                                        <div class="row flex-column">

                                                            @php
                                                                $itens = "<a href='#'
                                                                        class='btn btn-sm btn-outline-primary d-block'
                                                                        style='min-width: 7rem;'>
                                                                        Nome
                                                                    </a>
                                                                    <a href='#'
                                                                        class='btn btn-sm btn-outline-primary d-block'
                                                                        style='min-width: 7rem;'>
                                                                        CPF
                                                                    </a>
                                                                    <a href='#'
                                                                        class='btn btn-sm btn-outline-primary d-block'
                                                                        style='min-width: 7rem;'>
                                                                        Pai
                                                                    </a>
                                                                    <a href='#'
                                                                        class='btn btn-sm btn-outline-primary d-block'
                                                                        style='min-width: 7rem;'>
                                                                        Mãe
                                                                    </a>";
                                                            @endphp

                                                            <div class="d-grid d-sm-block">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-primary"
                                                                    data-bs-toggle="popover"
                                                                    data-bs-title="Informações Pessoais"
                                                                    data-bs-html="true" data-bs-content="<?= $itens ?>"
                                                                    style="min-width: 7rem;">
                                                                    Dados
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
