<li class="nav-item">
    <div class="nav-item-wrapper">
        <a class="nav-link dropdown-indicator collapsed" href="#nv-relatorio" role="button" data-bs-toggle="collapse"
            aria-expanded="false" aria-controls="nv-relatorio">
            <div class="d-flex align-items-center">
                <div class="dropdown-indicator-icon-wrapper mx-1">
                    <i class="bi bi-caret-right-fill dropdown-indicator-icon"></i>
                </div>
                <span class="nav-link-icon">
                    <i class="bi bi-suitcase-lg"></i>
                </span>
                <span class="nav-link-text">Relatórios</span>
            </div>
        </a>
        <div class="parent-wrapper">
            <ul class="nav parent collapse" id="nv-relatorio">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('relatorio.pagamentos-servicos.index') }}">
                        <span class="nav-link-text">Pagamentos Cadastrados</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</li>
