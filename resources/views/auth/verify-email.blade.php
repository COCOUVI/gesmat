<div class="container login-container">
    <div class="row justify-content-center w-100">
        <div class="col-md-6 col-lg-5">
            <div class="auth-form">

                <!-- Logo TOOLZY -->
                <div class="form-brand">
                    <i class="fas fa-tools fa-3x me-2 text-primary"></i>
                    <h2 style="font-weight: 700; color: var(--dark); margin-top: 15px;">J-MAT</h2>
                </div>

                <!-- Titre -->
                <h3 class="form-title">Vérification de l’adresse email</h3>

                <!-- Message principal -->
                <p class="form-subtitle">
                    Merci pour votre inscription ! Avant de commencer, veuillez vérifier votre adresse email
                    en cliquant sur le lien que nous venons de vous envoyer. <br>
                    Si vous n'avez pas reçu l'email, nous vous en enverrons un nouveau.
                </p>

                <!-- Message si renvoi effectué -->
                @if (session('status') == 'verification-link-sent')
                    <div class="alert alert-success">
                        Un nouveau lien de vérification a été envoyé à votre adresse email.
                    </div>
                @endif

                <!-- Formulaire de renvoi -->
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-login">
                        Renvoyer l’email de vérification
                    </button>
                </form>

                <!-- Bouton de déconnexion -->
                <div class="form-footer mt-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100" style="border-radius: 8px;">
                            <i class="fas fa-sign-out-alt me-2"></i> Se déconnecter
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
