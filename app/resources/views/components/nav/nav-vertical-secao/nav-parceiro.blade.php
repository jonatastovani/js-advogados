<li class="nav-item">
    <div class="nav-item-wrapper">
        <a class="nav-link dropdown-indicator" href="#nv-parceiro" role="button" data-bs-toggle="collapse"
            aria-expanded="true" aria-controls="nv-parceiro">
            <div class="d-flex align-items-center">
                <div class="dropdown-indicator-icon-wrapper mx-1">
                    <i class="bi bi-caret-right-fill dropdown-indicator-icon"></i>
                </div>
                <span class="nav-link-icon">
                    <i class="bi bi-suitcase-lg"></i>
                </span>
                <span class="nav-link-text">Parceiro</span>
            </div>
        </a>
        <div class="parent-wrapper">
            <ul class="nav parent collapse show" data-bs-parent="#navbarVerticalNav" id="nv-parceiro" style="">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pessoa.pessoa-fisica.parceiro.index') }}">
                        <span class="nav-link-text">Listagem de Parceiros</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pessoa.pessoa-fisica.parceiro.form') }}">
                        <span class="nav-link-text">Cadastrar Parceiro</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</li>
