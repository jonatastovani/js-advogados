<div class="row h-100">
    <form id="form{{ $sufixo }}" class="d-flex flex-column flex-md-row flex-lg-column flex-xl-row">
        <div class="row flex-row h-100 flex-grow-1">

            <div class="col d-flex flex-column mt-2">
                <textarea name="conteudo" id="conteudo{{ $sufixo }}" class="form-control flex-fill"></textarea>
            </div>
        </div>

        <div class="row flex-shrink-1">

            <div class="col d-flex flex-column ms-md-3 ms-lg-0 ms-xl-3 mt-md-2 mt-lg-0 mt-xl-2">
                <div class="d-grid d-sm-flex d-md-grid d-lg-flex d-xl-grid gap-2">
                    <div class="btn-group mt-2 mt-md-0 mt-lg-2 mt-xl-0">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Adicionar Objetos
                        </button>
                        <ul id="dropdownMenuAddObjetos{{ $sufixo }}" class="dropdown-menu">
                            {{-- <li>
                                <button type="button" id="btnAdicionarClientePF{{ $sufixo }}"
                                    class="dropdown-item" data-pessoa-tipo="PF">
                                    Pessoa Física
                                </button>
                            </li>
                            <li>
                                <button type="button" id="btnAdicionarClientePJ{{ $sufixo }}"
                                    class="dropdown-item" data-pessoa-tipo="PJ">
                                    Pessoa Jurídica
                                </button>
                            </li> --}}
                        </ul>
                    </div>

                    <div class="btn-group mt-2 mt-md-0 mt-lg-2 mt-xl-0">
                        <button class="btn btn-outline-danger btn-sm dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Remover Objetos
                        </button>
                        <ul id="dropdownMenuDropObjetos{{ $sufixo }}" class="dropdown-menu">
                            {{-- <li>
                                <button type="button" id="btnRemoverClientePF{{ $sufixo }}" class="dropdown-item"
                                    data-pessoa-tipo="PF">
                                    Pessoa Física
                                </button>
                            </li>
                            <li>
                                <button type="button" id="btnRemoverClientePJ{{ $sufixo }}" class="dropdown-item"
                                    data-pessoa-tipo="PJ">
                                    Pessoa Jurídica
                                </button>
                            </li> --}}
                        </ul>
                    </div>
                </div>

                <div id="accordionsObjetos{{ $sufixo }}"></div>

            </div>
        </div>
    </form>
</div>
