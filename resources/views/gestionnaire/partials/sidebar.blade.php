<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item nav-profile">
            <a href="#" class="nav-link">
                <div class="d-flex align-items-center">
                    <div class="profile-initials">AT</div>
                    <div class="nav-profile-text d-flex flex-column">
                        <span class="font-weight-bold mb-2">JASPETools</span>
                        <span class="text-secondary text-small">
                            <p>Gestionnaire</>
                        </span>
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
                            Inventaire
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('gestionnaire.tools.add') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.tools.add') }}">Ajouter un équipement</a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('gestionnaire.pannes.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.pannes.index') }}">
                            <i class="mdi mdi-alert-circle-outline"></i>
                            Pannes
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('gestionnaire.equipements.perdus') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.equipements.perdus') }}">
                            <i class="mdi mdi-emoticon-sad-outline"></i>
                            Retours planifiés
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
                            Demandes d'équipement
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('gestionnaire.affectations.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.affectations.index') }}">Liste des
                            affectations</a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('gestionnaire.affectations.create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.affectations.create') }}">Nouvelle
                            affectation</a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('liste.bons') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('liste.bons') }}">Bons</a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('gestionnaire.bons.bon_external_collaborator') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.bons.bon_external_collaborator') }}">Mouvement collaborateur externe</a>
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
                        <a class="nav-link" href="{{ route('gestionnaire.rapports.index') }}">Liste des rapports</a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('gestionnaire.rapports.create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.rapports.create') }}">Générer un rapport</a>
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
                        <a class="nav-link" href="{{ route('gestionnaire.collaborateurs.create') }}">Ajouter</a>
                    </li>
                    <li
                        class="nav-item {{ request()->routeIs('gestionnaire.collaborateurs.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gestionnaire.collaborateurs.index') }}">Liste</a>
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
