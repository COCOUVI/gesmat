<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <i class="fas fa-tools me-2 text-primary"></i>
            <span>J-MAT</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item mx-2">
                    <a class="nav-link active" href="{{url('/')}}">Accueil</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="#features">Fonctionnalités</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="#benefits">Avantages</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="#contact">Contact</a>
                </li>
                <li class="nav-item nav-btn">
                    <a href="{{ route('login') }}" class="btn btn-primary px-4">
                        <i class="fas fa-sign-in-alt me-2"></i>Connexion
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
