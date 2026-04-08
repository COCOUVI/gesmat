<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    {{-- Logo --}}
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo" href="index.html">
            <span class="toolzy-logo"><i class="fas fa-tools text-primary"></i>J-MAT</span>
        </a>
        <a class="navbar-brand brand-logo-mini" href="index.html">
            <i class="fas fa-tools text-primary"></i>
        </a>
    </div>

    {{-- Contenu principal de la navbar --}}
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-between flex-grow-1 px-3">

        {{-- Barre de recherche (responsive + centrée) --}}
        <form class="flex-grow-1 d-none d-md-flex justify-content-center" action="#" onsubmit="return false;">
            <div class="input-group" style="max-width: 600px; width: 100%;">
                <span
                    class="input-group-text border bg-primary rounded-start d-flex align-items-center justify-content-center"
                    style="width: 45px; padding: 0;" id="search-icon-desktop">
                    <i class="mdi mdi-magnify text-white" id="search-icon-desktop" style="font-size: 18px;"></i>
                </span>
                <input type="text" id="navbar-search" class="form-control border bg-white rounded-end"
                    placeholder="Rechercher un équipement..." style="height: 45px;">
            </div>
        </form>
        {{-- Barre de recherche mobile (visible uniquement en mobile) --}}
        <form class="d-flex d-md-none w-100 justify-content-center my-2" onsubmit="return false;">
            <div class="input-group" style="max-width: 100%; width: 100%;">
                <span
                    class="input-group-text border bg-primary rounded-start d-flex align-items-center justify-content-center"
                    style="width: 49px; padding: 0;" id="search-icon-mobile">
                    <i class="mdi mdi-magnify text-white" style="font-size: 18px;"></i>
                </span>
                <input type="text" id="navbar-search-mobile" class="form-control border bg-white rounded-end"
                    placeholder="Rechercher un équipement..." style="height: 45px;">
            </div>
        </form>


        {{-- Profil utilisateur --}}
        @php
            $initials = strtoupper(substr(auth()->user()->nom, 0, 1) . substr(auth()->user()->prenom, 0, 1));
        @endphp
        <ul class="navbar-nav navbar-nav-right ms-3">
            <li class="nav-item nav-profile dropdown">
                <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown"
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
                        <i class="mdi mdi-account-circle mr-2 text-primary"></i> Mon profil
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" style="background: none; border: none;">
                            <a class="dropdown-item">
                                <i class="mdi mdi-logout mr-2 text-primary"></i> Déconnexion
                            </a>
                        </button>
                    </form>
                </div>
            </li>
        </ul>

        {{-- Bouton hamburger mobile --}}
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center ms-2" type="button"
            data-bs-toggle="collapse" data-bs-target="#mobileMenu">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>



</nav>
