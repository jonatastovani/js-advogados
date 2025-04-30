@php
    use App\Helpers\TenantTypeDomainCustomHelper;
    $domainName = TenantTypeDomainCustomHelper::getDomainNameSelected();
    $domainCustomBln = TenantTypeDomainCustomHelper::getDomainCustomBln();
@endphp

<nav class="navbar navbar-vertical navbar-expand-lg border-end">
    <div class="navbar-vertical-content scrollbar">
        <div id="navbarMenuPrincipal" class="navbar navbar-collapse">
            <div class="offcanvas offcanvas-start" tabindex="-1" id="navbarNavDropdown"
                aria-labelledby="navbarNavDropdownLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="navbarNavDropdownLabel">{{ tenant('name') }}
                        <span class="current-domain-name">
                            @if ($domainName != '' && !$domainCustomBln)
                                • {{ $domainName }}
                            @endif
                        </span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav flex-column" id="navbarVerticalNav">
                        <li class="nav-item">
                            <div class="nav-item-wrapper">
                                <a class="nav-link" href="{{ Session::get('paginaDados')->home ?? "#"
                                 {{-- route('lobby') --}} }}">
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
                            @component('components.nav.nav-vertical-secao.nav-cliente')
                            @endcomponent
                            {{-- @component('components.nav.nav-vertical-secao.nav-documento')
                            @endcomponent --}}
                            @component('components.nav.nav-vertical-secao.nav-financeiro')
                            @endcomponent
                            @component('components.nav.nav-vertical-secao.nav-parceiro')
                            @endcomponent
                            @component('components.nav.nav-vertical-secao.nav-participacao')
                            @endcomponent
                            @component('components.nav.nav-vertical-secao.nav-relatorio')
                            @endcomponent
                            @component('components.nav.nav-vertical-secao.nav-servico')
                            @endcomponent
                            @component('components.nav.nav-vertical-secao.nav-sistema')
                            @endcomponent
                            @component('components.nav.nav-vertical-secao.nav-terceiro')
                            @endcomponent
                            @component('components.nav.nav-vertical-secao.nav-usuario')
                            @endcomponent
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
