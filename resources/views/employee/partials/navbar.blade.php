<div class="horizontal-menu">
    <nav class="navbar top-navbar col-lg-12 col-12 p-0">
        <div class="container-fluid">
            <div class="navbar-menu-wrapper d-flex align-items-center justify-content-between">
                <ul class="navbar-nav navbar-nav-left">
                    <li class="nav-item ms-0 me-5 d-lg-flex d-none">
                        <a href="#" class="nav-link horizontal-nav-left-menu"><i
                                class="mdi mdi-format-list-bulleted"></i></a>
                    </li>
                    <li class="nav-item dropdown">

                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list"
                            aria-labelledby="notificationDropdown">
                            <p class="mb-0 font-weight-normal float-left dropdown-header">Notifications</p>
                            <a class="dropdown-item preview-item">
                                <div class="preview-thumbnail">
                                    <div class="preview-icon bg-success">
                                        <i class="mdi mdi-check-circle mx-0"></i>
                                    </div>
                                </div>
                                <div class="preview-item-content">
                                    <h6 class="preview-subject font-weight-normal">Demande approuvée</h6>
                                    <p class="font-weight-light small-text mb-0 text-muted">
                                        Votre ordinateur portable a été approuvé
                                    </p>
                                </div>
                            </a>
                            <a class="dropdown-item preview-item">
                                <div class="preview-thumbnail">
                                    <div class="preview-icon bg-warning">
                                        <i class="mdi mdi-alert mx-0"></i>
                                    </div>
                                </div>
                                <div class="preview-item-content">
                                    <h6 class="preview-subject font-weight-normal">Panne signalée</h6>
                                    <p class="font-weight-light small-text mb-0 text-muted">
                                        Votre ticket a été pris en charge
                                    </p>
                                </div>
                            </a>
                        </div>
                    </li>
                </ul>
                <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
                    <a class="navbar-brand d-flex align-items-center" href="index.html">
                        <i class="fas fa-tools me-2" style="color: var(--primary);"></i>
                        <span class="logo-text  d-sm-inline">J-MAT</span>
                    </a>
                </div>

                <ul class="navbar-nav navbar-nav-right">
                    <li class="nav-item nav-profile dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"
                            id="profileDropdown">
                            <span class="nav-profile-name">{{ $user->nom }} {{ $user->prenom }}</span>
                            <span class="online-status"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown"
                            aria-labelledby="profileDropdown">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item"
                                    style="background: none; border: none; text-align: left; width: 100%;">
                                    <i class="mdi mdi-logout text-primary"></i>
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-toggle="horizontal-menu-toggle">
                    <span class="mdi mdi-menu"></span>
                </button>
            </div>
        </div>
    </nav>
    <nav class="bottom-navbar">
        <div class="container">
            <ul class="nav page-navigation">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard.employee') ? 'active' : '' }}"
                        href="{{ route('dashboard.employee') }}">
                        <i class="mdi mdi-view-dashboard menu-icon"></i>
                        <span class="menu-title">Tableau de bord</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('demande.equipement') ? 'active' : '' }}"
                        href="{{ route('demande.equipement') }}">
                        <i class="mdi mdi-laptop menu-icon"></i>
                        <span class="menu-title">Demander équipement</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('signaler.panne') ? 'active' : '' }}"
                        href="{{ route('signaler.panne') }}">
                        <i class="mdi mdi-alert-circle menu-icon"></i>
                        <span class="menu-title">Signaler pannes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('equipements.assignes') ? 'active' : '' }}"
                        href="{{ route('equipements.assignes') }}">
                        <i class="mdi mdi-format-list-bulleted menu-icon"></i>
                        <span class="menu-title">Équipements assignés</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('historique.pannes') ? 'active' : '' }}"
                        href="{{ route('historique.pannes') }}">
                        <i class="mdi mdi-alert menu-icon"></i>
                        <span class="menu-title">Liste Pannes</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

</div>


