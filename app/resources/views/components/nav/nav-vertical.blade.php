<nav class="navbar navbar-vertical navbar-expand-lg border-end">
    <div class="navbar-vertical-content scrollbar">
        <div id="navbarMenuPrincipal" class="navbar navbar-collapse">
            <div class="offcanvas offcanvas-start" tabindex="-1" id="navbarNavDropdown"
                aria-labelledby="navbarNavDropdownLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="navbarNavDropdownLabel">{{ config('sistema.nome') }}
                        - {{ Session::get('tenantDados')->nome }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav flex-column" id="navbarVerticalNav">
                        <li class="nav-item">
                            <div class="nav-item-wrapper">
                                <a class="nav-link" href="{{ Session::get('paginaDados')->home ?? route('lobby') }}">
                                    <div class="d-flex align-items-center">
                                        <span class="nav-link-icon">
                                            <i class="bi bi-house"></i>
                                        </span>
                                        <span class="nav-link-text">Home</span>
                                    </div>
                                </a>
                            </div>
                        </li>

                        @if (Request::is('admin') || Request::is('admin/*'))
                            @component('components.nav.nav-admin')
                            @endcomponent
                        @else
                            @component('components.nav.nav-vertical-secao.nav-servico')
                            @endcomponent
                            @component('components.nav.nav-vertical-secao.nav-participacao')
                            @endcomponent
                            @component('components.nav.nav-vertical-secao.nav-financeiro')
                            @endcomponent
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
