<li class="nav-item">
    <div class="nav-item-wrapper">
        <a class="nav-link dropdown-indicator" href="#nv-geren-info" role="button" data-bs-toggle="collapse"
            aria-expanded="true" aria-controls="nv-geren-info">
            <div class="d-flex align-items-center">
                <div class="dropdown-indicator-icon-wrapper mx-1">
                    <i class="bi bi-caret-right-fill dropdown-indicator-icon"></i>
                </div>
                <span class="nav-link-icon">
                    <i class="bi bi-person-fill-gear"></i>
                </span>
                <span class="nav-link-text">Gerenciamento de Usuários </span>
            </div>
        </a>
        <div class="parent-wrapper">
            <ul class="nav parent collapse show" data-bs-parent="#navbarVerticalNav" id="nv-geren-info" style="">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.usuarios.permissoes') }}">
                        <span class="nav-link-text">Permissões</span>
                    </a>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link dropdown-indicator" href="#nv-admin" data-bs-toggle="collapse"
                        aria-expanded="true" aria-controls="nv-admin">
                        <div class="d-flex align-items-center">
                            <div class="dropdown-indicator-icon-wrapper mx-1">
                                <i class="bi bi-caret-right-fill dropdown-indicator-icon"></i>
                            </div>
                            <span class="nav-link-text">Permissões</span>
                        </div>
                    </a>
                    <div class="parent-wrapper">
                        <ul class="nav parent collapse show" data-bs-parent="#e-commerce" id="nv-admin" style="">
                            <li class="nav-item"><a class="nav-link" href="apps/e-commerce/admin/add-product.html">
                                    <div class="d-flex align-items-center">
                                        <span class="nav-link-text">

                                        </span>
                                    </div>
                                </a><!-- more inner pages-->
                            </li>
                            <li class="nav-item"><a class="nav-link" href="apps/e-commerce/admin/products.html">
                                    <div class="d-flex align-items-center"><span class="nav-link-text">Products</span>
                                    </div>
                                </a><!-- more inner pages-->
                            </li>
                            <li class="nav-item"><a class="nav-link" href="apps/e-commerce/admin/customers.html">
                                    <div class="d-flex align-items-center"><span class="nav-link-text">Customers</span>
                                    </div>
                                </a><!-- more inner pages-->
                            </li>
                            <li class="nav-item"><a class="nav-link" href="apps/e-commerce/admin/customer-details.html">
                                    <div class="d-flex align-items-center"><span class="nav-link-text">Customer
                                            details</span></div>
                                </a><!-- more inner pages-->
                            </li>
                            <li class="nav-item"><a class="nav-link" href="apps/e-commerce/admin/orders.html">
                                    <div class="d-flex align-items-center"><span class="nav-link-text">Orders</span>
                                    </div>
                                </a><!-- more inner pages-->
                            </li>
                            <li class="nav-item"><a class="nav-link" href="apps/e-commerce/admin/order-details.html">
                                    <div class="d-flex align-items-center"><span class="nav-link-text">Order
                                            details</span></div>
                                </a><!-- more inner pages-->
                            </li>
                            <li class="nav-item"><a class="nav-link" href="apps/e-commerce/admin/refund.html">
                                    <div class="d-flex align-items-center"><span class="nav-link-text">Refund</span>
                                    </div>
                                </a><!-- more inner pages-->
                            </li>
                        </ul>
                    </div>
                </li> --}}
            </ul>
        </div>
    </div>
</li>
<li class="nav-item">
    <div class="nav-item-wrapper">
        <a class="nav-link dropdown-indicator" href="#nv-geren-perm" role="button" data-bs-toggle="collapse"
            aria-expanded="true" aria-controls="nv-geren-perm">
            <div class="d-flex align-items-center">
                <div class="dropdown-indicator-icon-wrapper mx-1">
                    <i class="bi bi-caret-right-fill dropdown-indicator-icon"></i>
                </div>
                <span class="nav-link-icon">
                    <i class="fa-solid fa-diagram-project"></i>
                </span>
                <span class="nav-link-text">Gerenciamento de Permissões</span>
            </div>
        </a>
        <div class="parent-wrapper">
            <ul class="nav parent collapse show" data-bs-parent="#navbarVerticalNav" id="nv-geren-perm" style="">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.permissoes.permissoes') }}">
                        <span class="nav-link-text">Permissões</span>
                    </a>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.permissoes.grupos') }}">
                        <span class="nav-link-text">Grupos</span>
                    </a>
                </li> --}}
            </ul>
        </div>
    </div>
</li>
