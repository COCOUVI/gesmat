<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    @php
        $initials = strtoupper(substr(auth()->user()->nom, 0, 1) . substr(auth()->user()->prenom, 0, 1));
    @endphp
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo" href="index.html">
            <span class="toolzy-logo"><i class="fas fa-tools text-primary"></i>J-MAT</span>
        </a>
        <a class="navbar-brand brand-logo-mini" href="index.html">
            <i class="fas fa-tools text-primary"></i>
        </a>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-stretch">
        <div class="search-field d-none d-md-block">
            <form class="d-flex align-items-center h-100" action="#">
                <div class="input-group">
                    <div class="input-group-prepend bg-transparent">
                        <i class="input-group-text border-0 mdi mdi-magnify"></i>
                    </div>
                    <input type="text" id="navbar-search" class="form-control bg-transparent border-0"
                        placeholder="Rechercher un équipement...">
                </div>
            </form>
        </div>

        <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item nav-profile dropdown">
                <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-toggle="dropdown"
                    aria-expanded="false">
                    <div class="d-flex align-items-center">
                        <div class="profile-initials">{{ $initials }}</div>
                        <div class="nav-profile-text">
                            <p class="mb-1 text-black">{{ auth()->user()->nom }} {{ auth()->user()->prenom }}</p>
                        </div>
                    </div>
                </a>
                <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="mdi mdi-account-circle mr-2 text-primary"></i>
                        Mon profil
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item" style="background:none;border:none;width:100%;text-align:left;">
                            <i class="mdi mdi-logout mr-2 text-primary"></i>
                            Déconnexion
                        </button>
                    </form>
                </div>
            </li>

        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
            data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>
