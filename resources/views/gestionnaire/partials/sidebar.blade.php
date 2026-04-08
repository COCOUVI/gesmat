@php
    $initials = strtoupper(substr(auth()->user()->nom, 0, 1) . substr(auth()->user()->prenom, 0, 1));
@endphp

<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item nav-profile">
            <a href="{{ route('admin.homedash') }}" class="nav-link">
                <div class="d-flex align-items-center">
                    <div class="profile-initials">{{ $initials }}</div>
                    <div class="nav-profile-text d-flex flex-column">
                        <span class="font-weight-bold mb-1">{{ auth()->user()->nom }} {{ auth()->user()->prenom }}</span>
                        <span class="text-secondary text-small">{{ ucfirst(auth()->user()->role) }}</span>
                    </div>
                </div>
            </a>
        </li>

        @php
            // Variables pour chaque section
            $dashboardActive = request()->routeIs('dashboard.gestionnaire') || request()->is('dashboard/gestionnaire');
            $stockActive =
                request()->routeIs('gestionnaire.tools.*') ||
                request()->routeIs('gestionnaire.pannes.*') ||
                request()->routeIs('gestionnaire.equipements.perdus');
            $mouvementsActive =
                request()->routeIs('gestionnaire.demandes.*') ||
                request()->routeIs('gestionnaire.affectations.*') ||
                request()->routeIs('liste.bons') ||
                request()->routeIs('gestionnaire.bons.bon_external_collaborator');
            $rapportActive = request()->routeIs('gestionnaire.rapports.*');
            $collabActive = request()->routeIs('gestionnaire.collaborateurs.*');
        @endphp

        <!-- Tableau de bord -->
        <li class="nav-item {{ $dashboardActive ? 'active' : '' }}">
            <a class="nav-link" href="{{ url('/dashboard/gestionnaire') }}">
                <span class="menu-title">Tableau de bord</span>
                <i class="mdi mdi-home menu-icon"></i>
            </a>
        </li>

        <li class="nav-item ">
            <a class="nav-link" data-toggle="collapse" href="#equipment-management"
                aria-expanded="{{ $stockActive ? 'true' : 'false' }}" aria-controls="equipment-management">
                <span class="menu-title">Stock & inventaire</span>
                <i class="menu-arrow"></i>
                <i class="mdi mdi-laptop menu-icon"></i>
            </a>
            <div class="collapse {{ $stockActive ? 'show' : '' }}" id="equipment-management">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link"
                            href="{{ route('gestionnaire.tools.list') }}">
                            <i class="mdi mdi-clipboard-text-outline"></i>
                            <span>Inventaire</span>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('gestionnaire.tools.add') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.tools.add') }}">
                            <i class="mdi mdi-plus-box-outline"></i>
                            <span>Ajouter un équipement</span>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('gestionnaire.pannes.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.pannes.index') }}">
                            <i class="mdi mdi-alert-circle-outline"></i>
                            <span>Pannes</span>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('gestionnaire.equipements.perdus') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.equipements.perdus') }}">
                            <i class="mdi mdi-emoticon-sad-outline"></i>
                            <span>Équipements non retournés</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item {{ $mouvementsActive ? 'active' : '' }}">
            <a class="nav-link" data-toggle="collapse" href="#affectation-management"
                aria-expanded="{{ $mouvementsActive ? 'true' : 'false' }}" aria-controls="affectation-management">
                <span class="menu-title">Mouvements</span>
                <i class="menu-arrow"></i>
                <i class="mdi mdi-swap-horizontal menu-icon"></i>
            </a>
            <div class="collapse {{ $mouvementsActive ? 'show' : '' }}" id="affectation-management">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item {{ request()->routeIs('gestionnaire.demandes.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.demandes.index') }}">
                            <i class="mdi mdi-cart-outline"></i>
                            <span>Demandes d'équipement</span>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('gestionnaire.affectations.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.affectations.index') }}">
                            <i class="mdi mdi-format-list-checks"></i>
                            <span>Liste des affectations</span>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('gestionnaire.affectations.create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.affectations.create') }}">
                            <i class="mdi mdi-plus-circle-outline"></i>
                            <span>Nouvelle affectation</span>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('liste.bons') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('liste.bons') }}">
                            <i class="mdi mdi-file-document-outline"></i>
                            <span>Bons</span>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('gestionnaire.bons.bon_external_collaborator') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.bons.bon_external_collaborator') }}">
                            <i class="mdi mdi-truck-delivery-outline"></i>
                            <span>Mouvement collaborateur externe</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Rapports -->
        <li class="nav-item {{ $rapportActive ? 'active' : '' }}">
            <a class="nav-link" data-toggle="collapse" href="#rapport-management"
                aria-expanded="{{ $rapportActive ? 'true' : 'false' }}" aria-controls="rapport-management">
                <span class="menu-title">Rapports</span>
                <i class="menu-arrow"></i>
                <i class="mdi mdi-file-chart menu-icon"></i>
            </a>
            <div class="collapse {{ $rapportActive ? 'show' : '' }}" id="rapport-management">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item {{ request()->routeIs('gestionnaire.rapports.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.rapports.index') }}">
                            <i class="mdi mdi-format-list-bulleted"></i>
                            <span>Liste des rapports</span>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('gestionnaire.rapports.create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.rapports.create') }}">
                            <i class="mdi mdi-file-plus-outline"></i>
                            <span>Générer un rapport</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Collaborateurs Externes -->
        <li class="nav-item {{ $collabActive ? 'active' : '' }}">
            <a class="nav-link" data-toggle="collapse" href="#collab-externes"
                aria-expanded="{{ $collabActive ? 'true' : 'false' }}" aria-controls="collab-externes">
                <span class="menu-title">Collaborateurs Externes</span>
                <i class="menu-arrow"></i>
                <i class="mdi mdi-account-group menu-icon"></i>
            </a>
            <div class="collapse {{ $collabActive ? 'show' : '' }}" id="collab-externes">
                <ul class="nav flex-column sub-menu">
                    <li
                        class="nav-item {{ request()->routeIs('gestionnaire.collaborateurs.create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.collaborateurs.create') }}">
                            <i class="mdi mdi-account-plus-outline"></i>
                            <span>Ajouter</span>
                        </a>
                    </li>
                    <li
                        class="nav-item {{ request()->routeIs('gestionnaire.collaborateurs.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.collaborateurs.index') }}">
                            <i class="mdi mdi-account-multiple-outline"></i>
                            <span>Liste</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                @method('post')
                <button type="submit" class="btn btn-danger">Deconnexion</button>
            </form>
        </li>
    </ul>
</nav>
