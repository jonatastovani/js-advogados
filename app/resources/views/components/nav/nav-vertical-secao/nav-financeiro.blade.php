<li class="nav-item">
    <div class="nav-item-wrapper">
        <a class="nav-link dropdown-indicator collapsed" href="#nv-financeiro" role="button" data-bs-toggle="collapse"
            aria-expanded="false" aria-controls="nv-financeiro">
            <div class="d-flex align-items-center">
                <div class="dropdown-indicator-icon-wrapper mx-1">
                    <i class="bi bi-caret-right-fill dropdown-indicator-icon"></i>
                </div>
                <span class="nav-link-icon">
                    <i class="bi bi-suitcase-lg"></i>
                </span>
                <span class="nav-link-text">Financeiro</span>
            </div>
        </a>
        <div class="parent-wrapper">
            <ul class="nav parent collapse" id="nv-financeiro" style="">
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ route('financeiro.index') }}">
                        <span class="nav-link-text">Dashboard Financeiro</span>
                    </a>
                </li> --}}
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('financeiro.lancamentos-agendamentos.index') }}">
                        <span class="nav-link-text">Agendamentos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('financeiro.balanco-repasse-parceiro.index') }}">
                        <span class="nav-link-text">Balanço de Repasse de Parceiro</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('financeiro.lancamentos-gerais.index') }}">
                        <span class="nav-link-text">Lançamentos Gerais</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('financeiro.lancamentos-servicos.index') }}">
                        <span class="nav-link-text">Lançamentos de Serviços</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('financeiro.movimentacao-conta.index') }}">
                        <span class="nav-link-text">Movimentações de Contas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('financeiro.painel-contas.index') }}">
                        <span class="nav-link-text">Painel de Contas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('financeiro.pagamentos-servicos.index') }}">
                        <span class="nav-link-text">Pagamentos de Serviços</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('financeiro.lancamentos-ressarcimentos.index') }}">
                        <span class="nav-link-text">Ressarcimentos/Compensações</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</li>
