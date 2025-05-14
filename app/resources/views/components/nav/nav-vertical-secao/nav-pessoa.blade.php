<li class="nav-item">
    <div class="nav-item-wrapper">
        <a class="nav-link dropdown-indicator collapsed" href="#nv-pessoa" role="button" data-bs-toggle="collapse"
            aria-expanded="false" aria-controls="nv-pessoa">
            <div class="d-flex align-items-center">
                <div class="dropdown-indicator-icon-wrapper mx-1">
                    <i class="bi bi-caret-right-fill dropdown-indicator-icon"></i>
                </div>
                <span class="nav-link-icon">
                    <i class="bi bi-suitcase-lg"></i>
                </span>
                <span class="nav-link-text">Pessoas</span>
            </div>
        </a>
        <div class="parent-wrapper">
            <ul class="nav parent collapse" id="nv-pessoa" style="">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pessoa.pessoa-fisica.index') }}">
                        <span class="nav-link-text">Listagem de Pessoas Físicas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pessoa.pessoa-fisica.form') }}">
                        <span class="nav-link-text">Cadastrar Pessoa Física</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pessoa.pessoa-juridica.index') }}">
                        <span class="nav-link-text">Listagem de Pessoas Jurídicas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pessoa.pessoa-juridica.form') }}">
                        <span class="nav-link-text">Cadastrar Pessoa Jurídica</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</li>
