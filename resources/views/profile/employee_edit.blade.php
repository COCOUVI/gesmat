@extends('employee.homedash')

@section('content')
    @php
        $selectedRole = in_array($user->role, ['employe', 'employé', 'employée'], true) ? 'employé' : $user->role;
    @endphp
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-1">Mon profil</h2>
                <p class="text-muted mb-0">Mettez à jour vos informations personnelles et votre mot de passe.</p>
            </div>
            <a href="{{ route('dashboard.employee') }}" class="btn btn-outline-secondary">
                <i class="mdi mdi-arrow-left me-1"></i>
                Retour au tableau de bord
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">Informations personnelles</h4>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nom</label>
                                    <input type="text" name="nom"
                                        class="form-control @error('nom') is-invalid @enderror"
                                        value="{{ old('nom', $user->nom) }}" required>
                                    @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Prénom</label>
                                    <input type="text" name="prenom"
                                        class="form-control @error('prenom') is-invalid @enderror"
                                        value="{{ old('prenom', $user->prenom) }}" required>
                                    @error('prenom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Adresse e-mail</label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Poste</label>
                                    <select class="form-select" disabled>
                                        @foreach ($posteOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($user->poste === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Service</label>
                                    <select class="form-select" disabled>
                                        @foreach ($serviceOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($user->service === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Rôle</label>
                                    <select class="form-select" disabled>
                                        @foreach ($roleOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($selectedRole === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save me-1"></i>
                                    Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">Sécurité</h4>

                        @if (session('success_password'))
                            <div class="alert alert-success">{{ session('success_password') }}</div>
                        @endif

                        <form action="{{ route('profile.password.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Mot de passe actuel</label>
                                <input type="password" name="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nouveau mot de passe</label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Confirmer le nouveau mot de passe</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="mdi mdi-lock-reset me-1"></i>
                                    Mettre à jour
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
