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
