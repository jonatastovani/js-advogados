<nav class="navbar navbar-top fixed-top bg-body-tertiary border-bottom border-opacity-50">
    <div class="collapse navbar-collapse justify-content-between d-flex">
        <div class="navbar-logo">
            <button class="navbar-toggler me-2 d-lg-none btn-sm" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon" style="max-width: 1.7rem"></span>
            </button>
            <a class="navbar-brand me-1 me-sm-3 " href="/">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center">
                        <img src="{{ asset(config('sistema.logo')) }}" alt="Logo {{ config('sistema.sigla_front') }}"
                            width="27">
                        <h5 class="logo-text ms-2 d-none d-sm-block">{{ config('sistema.sigla_front') }} -
                            {{ Session::get('tenantDados')->nome }}
                    </div>
                </div>
            </a>
        </div>
        <ul class="navbar-nav navbar-nav-icons flex-row">
            <li class="nav-item dropdown">
                <a class="nav-link lh-1 pe-0 show" id="navbarDropdownUser" href="#!" role="button"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="true">
                    <div class="avatar avatar-l ">
                        <img class="rounded-circle " src="{{ asset(config('sistema.logo')) }}" alt=""
                            width="40">
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end navbar-dropdown-caret py-0 dropdown-profile shadow border mt-2"
                    aria-labelledby="navbarDropdownUser" data-bs-popper="static">
                    <div class="card position-relative border-0">
                        <div class="card-body p-0">
                            <div class="text-center pt-4 pb-3">
                                <div class="avatar avatar-xl ">
                                    <img class="rounded-circle " src="{{ asset(config('sistema.logo')) }}"
                                        alt="" width="40">
                                </div>
                                <h6 class="mt-2 text-body-emphasis">Jerry Seinfield</h6>
                            </div>
                            <div class="mb-3 mx-3"><input class="form-control form-control-sm" id="statusUpdateInput"
                                    type="text" placeholder="Update your status"></div>
                        </div>
                        <div class="overflow-auto scrollbar" style="height: 10rem;">
                            <ul class="nav d-flex flex-column mb-2 pb-1">
                                <li class="nav-item"><a class="nav-link px-3 d-block" href="#!"> <svg
                                            xmlns="http://www.w3.org/2000/svg" width="16px" height="16px"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-user me-2 text-body align-bottom">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg><span>Profile</span></a></li>
                                <li class="nav-item"><a class="nav-link px-3 d-block" href="#!"><svg
                                            xmlns="http://www.w3.org/2000/svg" width="16px" height="16px"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-pie-chart me-2 text-body align-bottom">
                                            <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                                            <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                                        </svg>Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link px-3 d-block" href="#!"> <svg
                                            xmlns="http://www.w3.org/2000/svg" width="16px" height="16px"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-lock me-2 text-body align-bottom">
                                            <rect x="3" y="11" width="18" height="11" rx="2"
                                                ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                        </svg>Posts &amp; Activity</a></li>
                                <li class="nav-item"><a class="nav-link px-3 d-block" href="#!"> <svg
                                            xmlns="http://www.w3.org/2000/svg" width="16px" height="16px"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-settings me-2 text-body align-bottom">
                                            <circle cx="12" cy="12" r="3"></circle>
                                            <path
                                                d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                                            </path>
                                        </svg>Settings &amp; Privacy </a></li>
                                <li class="nav-item"><a class="nav-link px-3 d-block" href="#!"> <svg
                                            xmlns="http://www.w3.org/2000/svg" width="16px" height="16px"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-help-circle me-2 text-body align-bottom">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                            <line x1="12" y1="17" x2="12.01" y2="17">
                                            </line>
                                        </svg>Help Center</a></li>
                                <li class="nav-item"><a class="nav-link px-3 d-block" href="#!"> <svg
                                            xmlns="http://www.w3.org/2000/svg" width="16px" height="16px"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-globe me-2 text-body align-bottom">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="2" y1="12" x2="22" y2="12">
                                            </line>
                                            <path
                                                d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z">
                                            </path>
                                        </svg>Language</a></li>
                            </ul>
                        </div>
                        <div class="card-footer p-0 border-top border-translucent">
                            <ul class="nav d-flex flex-column my-3">
                                <li class="nav-item"><a class="nav-link px-3 d-block" href="#!"> <svg
                                            xmlns="http://www.w3.org/2000/svg" width="16px" height="16px"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-user-plus me-2 text-body align-bottom">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="8.5" cy="7" r="4"></circle>
                                            <line x1="20" y1="8" x2="20" y2="14">
                                            </line>
                                            <line x1="23" y1="11" x2="17" y2="11">
                                            </line>
                                        </svg>Add another account</a></li>
                            </ul>
                            <hr>
                            <div class="px-3"> <a class="btn btn-phoenix-secondary d-flex flex-center w-100"
                                    href="{{ route('logout') }}"> <svg xmlns="http://www.w3.org/2000/svg" width="16px"
                                        height="16px" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-log-out me-2">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12">
                                        </line>
                                    </svg>Sign out</a></div>
                            <div class="my-2 text-center fw-bold fs-10 text-body-quaternary"><a
                                    class="text-body-quaternary me-1" href="#!">Privacy policy</a>•<a
                                    class="text-body-quaternary mx-1" href="#!">Terms</a>•<a
                                    class="text-body-quaternary ms-1" href="#!">Cookies</a></div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</nav>
{{-- 
@push('scripts')
    <script type="module">
        const array = $('.scrollbar');
        for (let index = 0; index < array.length; index++) {
            new SimpleBar(array[index]);
        }
    </script>
@endpush --}}
