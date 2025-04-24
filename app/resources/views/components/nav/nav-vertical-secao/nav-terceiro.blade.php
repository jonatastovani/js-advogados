<li class="nav-item">
    <div class="nav-item-wrapper">
        <a class="nav-link dropdown-indicator collapsed" href="#nv-terceiro" role="button" data-bs-toggle="collapse"
            aria-expanded="false" aria-controls="nv-terceiro">
            <div class="d-flex align-items-center">
                <div class="dropdown-indicator-icon-wrapper mx-1">
                    <i class="bi bi-caret-right-fill dropdown-indicator-icon"></i>
                </div>
                <span class="nav-link-icon">
                    <i class="bi bi-suitcase-lg"></i>
                </span>
                <span class="nav-link-text">Terceiro</span>
            </div>
        </a>
        <div class="parent-wrapper">
            <ul class="nav parent collapse" id="nv-terceiro" style="">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pessoa.pessoa-fisica.terceiro.index') }}">
                        <span class="nav-link-text">Listagem de Terceiros PF</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pessoa.pessoa-fisica.terceiro.form') }}">
                        <span class="nav-link-text">Cadastrar Terceiro PF</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pessoa.pessoa-juridica.terceiro.index') }}">
                        <span class="nav-link-text">Listagem de Terceiros PJ</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pessoa.pessoa-juridica.terceiro.form') }}">
                        <span class="nav-link-text">Cadastrar Terceiro PJ</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</li>
