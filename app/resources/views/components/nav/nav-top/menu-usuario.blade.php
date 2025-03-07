@php
    use App\Enums\TenantTypeEnum;

    $user = Auth::user();
@endphp

<ul class="navbar-nav navbar-nav-icons flex-row">
    <li class="nav-item dropdown">
        <a class="nav-link lh-1 pe-0 show" id="navbarDropdownUser" href="#!" role="button"
            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
            <div class="avatar avatar-l ">
                <img class="rounded-circle border" src="{{ asset(config('sistema.logo')) }}" alt=""
                    width="40">
            </div>
        </a>
        <div class="dropdown-menu dropdown-menu-end navbar-dropdown-caret py-0 dropdown-profile shadow border mt-2 show"
            aria-labelledby="navbarDropdownUser" data-bs-popper="static">
            <div class="card position-relative border-0">
                <div class="card-body p-0">
                    <div class="text-center p-2">
                        <div class="avatar avatar-xl ">
                            <img class="rounded-circle border" src="{{ asset(config('sistema.logo')) }}" alt=""
                                width="40">
                        </div>
                        <h6 class="mt-2 text-body-emphasis">{{ $user->name }}</h6>
                    </div>
                </div>
                <div class="overflow-auto scrollbar">
                    <ul class="nav flex-column mb-2 pb-1">

                        {{-- Svgs usados no menu --}}
                        @component('components.nav.nav-top.menu-usuario.svgs')
                        @endcomponent

                        {{-- Temas de visualização para o usuário --}}
                        @component('components.nav.nav-top.menu-usuario.alterar-tema')
                        @endcomponent

                        @if (tenant('tenant_type_id') == TenantTypeEnum::ADVOCACIA_MANUAL->value)
                            {{-- Selecionar o domínio manualmente pelo usuário (Tenant do Tipo 4 - Identificação de domínio manual) --}}
                            @component('components.nav.nav-top.menu-usuario.alterar-domain')
                            @endcomponent
                        @endif

                        {{-- <li class="nav-item">
                            <a class="nav-link px-3 d-block" href="#!"> <svg xmlns="http://www.w3.org/2000/svg"
                                    width="16px" height="16px" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-user me-2 text-body align-bottom">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg><span>Profile</span></a>
                        </li> --}}

                    </ul>

                </div>
                <p class="form-text text-end fw-bolder my-1 mx-3">By {{ config('sistema.nome') }}</p>
                <div class="card-footer p-0 border-top border-translucent">
                    <div class="px-3">
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <button type="submit" class="btn w-100">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                    {{-- <div class="my-2 text-center fw-bold fs-10 text-body-quaternary"><a
                            class="text-body-quaternary me-1" href="#!">Privacy policy</a>•<a
                            class="text-body-quaternary mx-1" href="#!">Terms</a>•<a
                            class="text-body-quaternary ms-1" href="#!">Cookies</a></div> --}}
                </div>
            </div>
        </div>
    </li>
</ul>
