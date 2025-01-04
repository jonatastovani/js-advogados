<li class="nav-item">
    <div class="nav-item-wrapper">
        <a class="nav-link dropdown-indicator" href="#nv-sistema" role="button" data-bs-toggle="collapse"
            aria-expanded="true" aria-controls="nv-configuracao">
            <div class="d-flex align-items-center">
                <div class="dropdown-indicator-icon-wrapper mx-1">
                    <i class="bi bi-caret-right-fill dropdown-indicator-icon"></i>
                </div>
                <span class="nav-link-icon">
                    <i class="bi bi-suitcase-lg"></i>
                </span>
                <span class="nav-link-text">Sistema</span>
            </div>
        </a>
        <div class="parent-wrapper">
            <ul class="nav parent collapse show" data-bs-parent="#navbarVerticalNav" id="nv-sistema" style="">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('sistema.dados-da-empresa.form') }}">
                        <span class="nav-link-text">Dados da Empresa</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('sistema.configuracao.form') }}">
                        <span class="nav-link-text">Configurações do Sistema</span>
                    </a>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ route('sistema.configuracao.preenchimento-automatico.form') }}">
                        <span class="nav-link-text">Preenchimento Automático</span>
                    </a>
                </li> --}}
            </ul>
        </div>
    </div>
</li>
