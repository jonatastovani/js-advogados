<li class="nav-item">
    <div class="nav-item-wrapper">
        <a class="nav-link dropdown-indicator" href="#nv-cliente" role="button" data-bs-toggle="collapse"
            aria-expanded="true" aria-controls="nv-cliente">
            <div class="d-flex align-items-center">
                <div class="dropdown-indicator-icon-wrapper mx-1">
                    <i class="bi bi-caret-right-fill dropdown-indicator-icon"></i>
                </div>
                <span class="nav-link-icon">
                    <i class="bi bi-suitcase-lg"></i>
                </span>
                <span class="nav-link-text">Cliente</span>
            </div>
        </a>
        <div class="parent-wrapper">
            <ul class="nav parent collapse show" data-bs-parent="#navbarVerticalNav" id="nv-cliente" style="">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pessoa.pessoa-fisica.cliente.index') }}">
                        <span class="nav-link-text">Listagem de Clientes PF</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pessoa.pessoa-juridica.cliente.index') }}">
                        <span class="nav-link-text">Listagem de Clientes PJ</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</li>
