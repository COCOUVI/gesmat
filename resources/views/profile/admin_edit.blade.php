@php
    $layout = auth()->user()->role === 'admin' ? 'admin.layouts.adminlay' : 'gestionnaire.layouts.gestionlay';
@endphp

@extends($layout)

@section('content')
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-toolzy-primary text-white mr-2">
                <i class="mdi mdi-account-circle"></i>
            </span>
            Mon profil
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Informations personnelles</li>
            </ul>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-7 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Mettre à jour mes informations</h4>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        @php
                            $isAdministrator = auth()->user()->role === 'admin';
                            $selectedRole = old('role', $user->role);
                            if (in_array($selectedRole, ['employe', 'employé', 'employée'], true)) {
                                $selectedRole = 'employé';
                            }
                        @endphp

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
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
                                <label class="form-label">Rôle</label>
                                <select name="role" class="form-select @error('role') is-invalid @enderror" @disabled(!$isAdministrator)>
                                    @foreach ($roleOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($selectedRole === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Poste</label>
                                <select name="poste" class="form-select @error('poste') is-invalid @enderror" @disabled(!$isAdministrator)>
                                    @foreach ($posteOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('poste', $user->poste) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('poste')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Service</label>
                                <select name="service" class="form-select @error('service') is-invalid @enderror" @disabled(!$isAdministrator)>
                                    @foreach ($serviceOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('service', $user->service) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('service')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save me-1"></i>
                                Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Modifier mon mot de passe</h4>

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
                                Mettre à jour le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
