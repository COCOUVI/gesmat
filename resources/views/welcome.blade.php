@extends('layouts.app')
@section('style')
@include("layouts.style")
@endsection
@section('content')
    <section class="hero-section">
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h1 class="display-4 fw-bold mb-4">Optimisez votre parc <br>matériel avec <span
                            class="text-warning">J-MAT</span></h1>
                    <p class="lead mb-5">La solution complète pour une gestion efficace et traçable de votre inventaire
                        professionnel</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('login') }}" class="btn btn-accent btn-lg px-5 py-3 pulse-animation">
                            <i class="fas fa-play-circle me-2"></i>Commencer
                        </a>
                    </div>
                    <div class="mt-5 d-flex flex-wrap gap-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-white p-2 rounded-circle me-3">
                                <i class="fas fa-check-circle text-success fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">+95%</h5>
                                <p class="mb-0 text-white-50">Efficacité accrue</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="bg-white p-2 rounded-circle me-3">
                                <i class="fas fa-clock text-primary fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">-70%</h5>
                                <p class="mb-0 text-white-50">Temps de gestion</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="position-relative">
                        <!-- Nouvelle image significative pour la gestion de matériel -->
                        <img src="https://images.unsplash.com/photo-1573164713988-8665fc963095?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&h=600&q=80"
                            alt="Gestion de matériel professionnelle" class="img-fluid hero-illustration">


                        <span class="stats-badge position-absolute top-0 start-0 m-3">
                            <i class="fas fa-laptop me-2 text-success"></i>+245 matériels
                        </span>
                        <span class="stats-badge position-absolute bottom-0 end-0 m-3">
                            <i class="fas fa-users me-2 text-primary"></i>37 utilisateurs
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Avantages -->
    <section id="benefits" class="benefits-section">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <h2 class="fw-bold mb-3">Pourquoi choisir J-MAT ?</h2>
                    <p class="text-muted">Découvrez les avantages de notre solution pour votre entreprise</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-chart-line fa-lg"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Optimisation des coûts</h4>
                            <p>Réduisez les pertes matérielles et les achats inutiles grâce à une gestion précise de votre
                                inventaire.</p>
                        </div>
                    </div>

                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-search fa-lg"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Traçabilité complète</h4>
                            <p>Suivez chaque matériel de son acquisition à sa maintenance, avec un historique complet.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Gain de temps</h4>
                            <p>Automatisez vos processus de gestion et gagnez plusieurs heures par semaine.</p>
                        </div>
                    </div>

                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-shield-alt fa-lg"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Sécurité des données</h4>
                            <p>Protégez vos informations avec des sauvegardes automatiques et un système de sécurité avancé.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Fonctionnalités -->
    <section id="features" class="py-6 bg-white">
        <div class="container">
            <div class="row justify-content-center mb-6">
                <div class="col-lg-8 text-center">
                    <h2 class="fw-bold mb-3">Fonctionnalités Clés</h2>
                    <p class="text-muted">Découvrez comment J-MAT optimise votre gestion de matériel</p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Gestionnaire -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card card p-4 h-100 position-relative">
                        <span class="feature-badge">Gestionnaire</span>
                        <div class="icon-container">
                            <i class="fas fa-cogs fa-2x text-primary"></i>
                        </div>
                        <h4 class="text-center mb-3">Gestion Complète</h4>
                        <div class="feature-content">
                            <ul class="feature-list">
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>Ajout/modification de matériel</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>Gestion des demandes</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>Génération de bons</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>Suivi des maintenances</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Administrateur -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card card p-4 h-100 position-relative">
                        <span class="feature-badge">Administrateur</span>
                        <div class="icon-container">
                            <i class="fas fa-user-shield fa-2x text-primary"></i>
                        </div>
                        <h4 class="text-center mb-3">Gestion Utilisateurs</h4>
                        <div class="feature-content">
                            <ul class="feature-list">
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>Création de comptes</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>Gestion des droits</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>Suivi des activités</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>Rapports personnalisés</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Employé -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card card p-4 h-100 position-relative">
                        <span class="feature-badge">Employé</span>
                        <div class="icon-container">
                            <i class="fas fa-user-tie fa-2x text-primary"></i>
                        </div>
                        <h4 class="text-center mb-3">Espace Personnel</h4>
                        <div class="feature-content">
                            <ul class="feature-list">
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>Demandes de matériel</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>Suivi des affectations</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>Signalement de pannes</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
