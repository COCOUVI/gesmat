@extends('admin.layouts.adminlay')
@section('content')
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-toolzy-primary text-white mr-2">
                <i class="mdi mdi-home"></i>
            </span>
            Tableau de bord
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">
                    <span></span>Vue d'ensemble
                    <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"></i>
                </li>
            </ul>
        </nav>
    </div>
    <div class="row">
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card bg-gradient-toolzy-primary card-img-holder text-white">
                <div class="card-body">
                    <img src="images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                    <h4 class="font-weight-normal mb-3">Total Équipements
                        <i class="mdi mdi-laptop mdi-24px float-right"></i>
                    </h4>
                    <h2 class="mb-5">{{ $nbr_equipement }}</h2>
                    {{-- <h6 class="card-text">Augmentation de 15% ce mois</h6> --}}
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card bg-gradient-toolzy-secondary card-img-holder text-white">
                <div class="card-body">
                    <img src="images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                    <h4 class="font-weight-normal mb-3">Équipements affectés
                        <i class="mdi mdi-account-check mdi-24px float-right"></i>
                    </h4>
                    <h2 class="mb-5"> {{$nbr_affect}}</h2>
                     <h6 class="card-text"> {{$nbr_affect===0?0:number_format(($nbr_affect/$nbr_equipement)*100,0) }}% du total des équipements</h6>
                </div>
            </div>
        </div>
        @if (auth()->user()->role == 'admin')
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card bg-gradient-toolzy-success card-img-holder text-white">
                    <div class="card-body">
                        <img src="images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Utilisateurs actifs
                            <i class="mdi mdi-account-multiple mdi-24px float-right"></i>
                        </h4>
                        <h2 class="mb-5">{{ $nbr_user }}</h2>
                        <h6 class="card-text">Augmentation de {{$growth<0?-$growth:$growth}} % ce mois</h6>
                    </div>
                </div>
            </div>
        @else
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card bg-gradient-toolzy-danger card-img-holder text-white">
                    <div class="card-body">
                        <img src="images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Équipements en panne
                            <i class="mdi mdi-alert mdi-24px float-right"></i>
                        </h4>
                        <h2 class="mb-5">{{ $nbr_panne}}</h2>
                        <h6 class="card-text">Résolu</h6>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="row">
        <div class="col-md-7 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="clearfix">
                        <h4 class="card-title float-left">Statistiques d'utilisation des équipements
                        </h4>
                        <div id="visit-sale-chart-legend"
                            class="rounded-legend legend-horizontal legend-top-right float-right">
                        </div>
                    </div>
                    <canvas id="visit-sale-chart" class="mt-4"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Distribution des équipements</h4>
                    <canvas id="traffic-chart"></canvas>
                    <div id="traffic-chart-legend" class="rounded-legend legend-vertical legend-bottom-left pt-4"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function generateColor(index) {
            const colors = [
                '#36d7e8', '#b194fa', '#ffc107', '#e91e63', '#4caf50', '#607d8b',
                '#9c27b0', '#ff5722', '#03a9f4', '#8bc34a', '#795548', '#00bcd4'
            ];
            return colors[index % colors.length]; // Tourne si plus de couleurs que de catégories
        }

        const equipementsParMois = {!! json_encode(array_values($statsParMois)) !!};
        const moisLabels = {!! json_encode(
            array_map(
                fn($i) => \Carbon\Carbon::create()->month($i)->locale('fr_FR')->isoFormat('MMM'),
                array_keys($statsParMois),
            ),
        ) !!};
        const distribution = {!! json_encode($distribution) !!};
        const distributionLabels = distribution.map(item => item.label);
        const distributionData = distribution.map(item => item.count);
        window.equipementsParMois = {!! json_encode(array_values($statsParMois)) !!};
        window.moisLabels = {!! json_encode(
            array_map(
                fn($i) => \Carbon\Carbon::create()->month($i)->locale('fr_FR')->isoFormat('MMM'),
                array_keys($statsParMois),
            ),
        ) !!};
    </script>
@endpush
