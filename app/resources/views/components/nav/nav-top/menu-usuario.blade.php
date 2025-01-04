@php
    $user = Auth::user();
@endphp

<ul class="navbar-nav navbar-nav-icons flex-row">
    <li class="nav-item dropdown">
        <a class="nav-link lh-1 pe-0 {{-- show --}}" id="navbarDropdownUser" href="#!" role="button"
            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="true">
            <div class="avatar avatar-l ">
                <img class="rounded-circle border" src="{{ asset(config('sistema.logo')) }}" alt=""
                    width="40">
            </div>
        </a>
        <div class="dropdown-menu dropdown-menu-end navbar-dropdown-caret py-0 dropdown-profile shadow border mt-2 {{-- show --}}"
            aria-labelledby="navbarDropdownUser" {{-- data-bs-popper="static" --}}>
            <div class="card position-relative border-0">
                <div class="card-body p-0">
                    <div class="text-center p-2">
                        <div class="avatar avatar-xl ">
                            <img class="rounded-circle border" src="{{ asset(config('sistema.logo')) }}" alt=""
                                width="40">
                        </div>
                        <h6 class="mt-2 text-body-emphasis">{{ $user->name }}</h6>
                    </div>
                    {{-- <div class="mb-3 mx-3"><input class="form-control form-control-sm" id="statusUpdateInput"
                            type="text" placeholder="Update your status"></div> --}}
                </div>
                <div class="overflow-auto scrollbar" style="height: 10rem;">
                    <ul class="nav d-flex flex-column mb-2 pb-1">

                        <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
                            <symbol id="check2" viewBox="0 0 16 16">
                                <path
                                    d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" />
                            </symbol>
                            <symbol id="circle-half" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z" />
                            </symbol>
                            <symbol id="moon-stars-fill" viewBox="0 0 16 16">
                                <path
                                    d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z" />
                                <path
                                    d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z" />
                            </symbol>
                            <symbol id="sun-fill" viewBox="0 0 16 16">
                                <path
                                    d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z" />
                            </symbol>
                        </svg>

                        <li class="nav-item d-block dropdown bd-mode-toggle w-100">
                            <button class="btn nav-link w-100 dropdown-toggle" id="bd-theme" type="button"
                                aria-expanded="false" data-bs-toggle="dropdown" aria-label="Alterar tema (auto)">
                                <svg class="bi theme-icon-active" width="1em" height="1em">
                                    <use href="#circle-half"></use>
                                </svg>
                                <span class="ms-2">Tema</span>
                                <span class="visually-hidden" id="bd-theme-text">Alterar tema</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
                                <li>
                                    <button type="button" class="dropdown-item d-flex align-items-center"
                                        data-bs-theme-value="light" aria-pressed="false">
                                        <svg class="bi me-2 opacity-50" width="1em" height="1em">
                                            <use href="#sun-fill"></use>
                                        </svg>
                                        Light
                                        <svg class="bi ms-auto d-none" width="1em" height="1em">
                                            <use href="#check2"></use>
                                        </svg>
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item d-flex align-items-center"
                                        data-bs-theme-value="dark" aria-pressed="false">
                                        <svg class="bi me-2 opacity-50" width="1em" height="1em">
                                            <use href="#moon-stars-fill"></use>
                                        </svg>
                                        Dark
                                        <svg class="bi ms-auto d-none" width="1em" height="1em">
                                            <use href="#check2"></use>
                                        </svg>
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item d-flex align-items-center active"
                                        data-bs-theme-value="auto" aria-pressed="true">
                                        <svg class="bi me-2 opacity-50" width="1em" height="1em">
                                            <use href="#circle-half"></use>
                                        </svg>
                                        Auto
                                        <svg class="bi ms-auto d-none" width="1em" height="1em">
                                            <use href="#check2"></use>
                                        </svg>
                                    </button>
                                </li>
                            </ul>
                        </li>

                        {{-- <li class="nav-item">
                            <a class="nav-link px-3 d-block" href="#!"> <svg xmlns="http://www.w3.org/2000/svg"
                                    width="16px" height="16px" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-user me-2 text-body align-bottom">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg><span>Profile</span></a>
                        </li>
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
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2">
                                    </rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>Posts &amp; Activity</a></li>
                        <li class="nav-item"><a class="nav-link px-3 d-block" href="#!"> <svg
                                    xmlns="http://www.w3.org/2000/svg" width="16px" height="16px"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-settings me-2 text-body align-bottom">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path
                                        d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                                    </path>
                                </svg>Settings &amp; Privacy </a></li>
                        <li class="nav-item"><a class="nav-link px-3 d-block" href="#!"> <svg
                                    xmlns="http://www.w3.org/2000/svg" width="16px" height="16px"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-help-circle me-2 text-body align-bottom">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                    <line x1="12" y1="17" x2="12.01" y2="17">
                                    </line>
                                </svg>Help Center</a></li>
                        <li class="nav-item"><a class="nav-link px-3 d-block" href="#!">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16px" height="16px"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-globe me-2 text-body align-bottom">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="2" y1="12" x2="22" y2="12">
                                    </line>
                                    <path
                                        d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z">
                                    </path>
                                </svg>Language</a></li> --}}

                    </ul>

                </div>
                <div class="card-footer p-0 border-top border-translucent">
                    {{-- <ul class="nav d-flex flex-column my-3">
                        <li class="nav-item"><a class="nav-link px-3 d-block" href="#!"> <svg
                                    xmlns="http://www.w3.org/2000/svg" width="16px" height="16px"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-user-plus me-2 text-body align-bottom">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="8.5" cy="7" r="4"></circle>
                                    <line x1="20" y1="8" x2="20" y2="14">
                                    </line>
                                    <line x1="23" y1="11" x2="17" y2="11">
                                    </line>
                                </svg>Add another account</a></li>
                    </ul>
                    <hr> --}}
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
