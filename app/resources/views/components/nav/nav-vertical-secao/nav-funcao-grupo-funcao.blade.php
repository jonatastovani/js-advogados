<li class="nav-item">
    <div class="nav-item-wrapper">
        <a class="nav-link dropdown-indicator" href="#nv-preset-copart" role="button" data-bs-toggle="collapse"
            aria-expanded="true" aria-controls="nv-preset-copart">
            <div class="d-flex align-items-center">
                <div class="dropdown-indicator-icon-wrapper mx-1">
                    <i class="bi bi-caret-right-fill dropdown-indicator-icon"></i>
                </div>
                <span class="nav-link-icon">
                    <i class="bi bi-suitcase-lg"></i>
                </span>
                <span class="nav-link-text">Presets de Participação</span>
            </div>
        </a>
        <div class="parent-wrapper">
            <ul class="nav parent collapse show" data-bs-parent="#navbarVerticalNav" id="nv-preset-copart">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('servico.participacao.index') }}">
                        <span class="nav-link-text">Listagem de Presets</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('servico.participacao.form') }}">
                        <span class="nav-link-text">Cadastrar Preset de Participação</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</li>