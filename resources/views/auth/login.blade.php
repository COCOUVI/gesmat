@extends('layouts.app')
@section('style')
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #1e40af;
            --accent: #f59e0b;
            --dark: #1e293b;
        }

        body {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Styles de la navbar */
        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--dark) !important;
            font-size: 1.5rem;
        }

        .nav-link {
            font-weight: 500;
            color: var(--dark) !important;
            transition: all 0.3s;
        }

        .nav-link:hover {
            color: var(--primary) !important;
        }

        /* Styles du formulaire */
        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 40px 0;
        }

        .auth-form {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }

        .form-brand {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-title {
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .form-subtitle {
            color: #6c757d;
            font-weight: 400;
            margin-bottom: 30px;
        }

        .form-control {
            height: 50px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .btn-login {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 8px;
            height: 50px;
            font-weight: 500;
            width: 100%;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .btn-login:hover {
            background: linear-gradient(to right, var(--secondary), var(--primary));
            color: white;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
        }

        /* Styles du footer */
        footer {
            background: var(--dark);
            color: rgba(255, 255, 255, 0.8);
            padding: 40px 0 20px;
        }

        footer h5 {
            color: white;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s;
        }

        footer a:hover {
            color: white;
            text-decoration: underline;
        }

        .footer-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 30px 0 20px;
        }

        .social-icons a {
            display: inline-block;
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            text-align: center;
            line-height: 36px;
            margin-right: 10px;
            transition: all 0.3s;
        }

        .social-icons a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }
    </style>
@endsection
@section('content')
    <div class="container login-container">
        <div class="row justify-content-center w-100">
            <div class="col-md-6 col-lg-5">
                <div class="auth-form">
                    <div class="form-brand">
                        <i class="fas fa-tools fa-3x me-2 text-primary"></i>
                        <h2 style="font-weight: 700; color: var(--dark); margin-top: 15px;">J-MAT</h2>
                    </div>

                    <h3 class="form-title">Bienvenue !</h3>
                    <p class="form-subtitle">Connectez-vous pour accéder à votre espace</p>

                    <form method="POST" action="{{ route('Postlogin') }}">
                        @method('POST')
                        @csrf
                        @if ($errors->has('email'))
                            <div class="alert alert-danger">
                                {{ $errors->first('email') }}
                            </div>
                        @endif


                        <div class="form-group">
                            <span class="fw-bold"> Email</span>
                            <input type="email" class="form-control form-control-lg" id="emailInput" name="email"
                                placeholder="Email ou nom d'utilisateur" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="passwordInput" class="fw-bold mb-2">Mot de passe</label>

                            <div class="position-relative">
                                <input type="password" class="form-control form-control-lg pe-5" id="passwordInput"
                                    name="password" placeholder="Mot de passe" required>

                                <!-- Icône intégrée dans le input -->
                                <span class="position-absolute top-50 end-0 translate-middle-y me-3"
                                    style="cursor: pointer;" onclick="togglePassword()">
                                    <i class="fas fa-eye text-secondary" id="eyeIcon"></i>
                                </span>
                            </div>
                        </div>


                        <button type="submit" class="btn btn-login">
                            CONNEXION
                        </button>

                        <!-- Ligne modifiée pour l'alignement -->
                        <div class="form-options d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <a href="{{ route('password.request') }}" class="text-primary">Mot de passe oublié ?</a>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("passwordInput");
            const eyeIcon = document.getElementById("eyeIcon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }
    </script>
@endpush
