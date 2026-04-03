<div class="sidebar-wrapper">
    <nav class="mt-2">
        <!--begin::Sidebar Menu-->
        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation"
            aria-label="Main navigation" data-accordion="false" id="navigation">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}"
                    class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-speedometer"></i>
                    <p>
                        Dashboard
                        <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="./index.html" class="nav-link">
                            <i class="nav-icon bi bi-circle"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- <li class="nav-header">EXAMPLES</li> --}}
            {{-- preciso deixar o link ativo com base no link colocar a classe active --}}
            <li class="nav-item">
                <a href="{{ route('importar_clientes.index') }}"
                    class="nav-link {{ request()->routeIs('importar_clientes.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <p>Clientes</p>
                </a>
            </li>
        </ul>
        <!--end::Sidebar Menu-->
    </nav>
</div>