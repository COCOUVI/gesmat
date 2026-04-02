<!DOCTYPE html>
<html lang="fr">

<head>
    <!-- Required meta tags -->
    <base href="/public">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>JASPETools - Gestionnaire</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/iconfonts/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
    <!-- endinject -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="images/favicon.png" />

    <!-- Logo personnalisé -->
    <div class="bg-white p-2 d-flex align-items-center shadow-sm" style="height: 60px;">
        <img src="{{ asset('images/JASPETools.png') }}" alt="Logo JASPETools" height="40" class="me-2">
        <span class="fs-4 fw-bold text-dark">JASPETools</span>
    </div>

    @include('gestionnaire.partials.style')

    <style>
    body {
        background-image: url("images/tools.jpg"); /* adapte le chemin */
        background-size: cover;
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-position: center;
    }

    /* Optionnel : pour ajouter un effet flouté ou une couleur superposée */

    .content-wrapper {
    background: transparent;
    padding: 20px;
    }   
</style>

</head>

<body>
    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        @include('gestionnaire.partials.navbar')
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            @include('gestionnaire.partials.sidebar')
            <!-- partial -->
            <div class="main-panel">
                <div class="content-wrapper">
                    @yield('content')
                    <!-- Gestion des utilisateurs -->


                    <!-- Gestion des équipements -->

                    <!-- Affectations récentes -->

                    <!-- Rapports récents -->

                    <!-- content-wrapper ends -->
                    <!-- partial:partials/_footer.html -->
                    <!-- partial -->
                </div>
                @include('gestionnaire.partials.footer')
                <!-- main-panel ends -->
            </div>
            <!-- page-body-wrapper ends -->
        </div>
        <!-- container-scroller -->
        @stack('popups')
        @stack("scripts")
        <!-- plugins:js -->
        <script src="vendors/js/vendor.bundle.base.js"></script>
        <script src="vendors/js/vendor.bundle.addons.js"></script>
        <!-- endinject -->
        <!-- Plugin js for this page-->
        <!-- End plugin js for this page-->
        <!-- inject:js -->
        <script src="js/off-canvas.js"></script>
        <script src="js/misc.js"></script>
        <script src="js/template.js"></script>
        <!-- endinject -->
        <!-- Custom js for this page-->
        <script src="js/dashboard.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('table.smart-data-table').forEach(function(table) {
                    if (table.dataset.smartTableReady === '1') {
                        return;
                    }

                    table.dataset.smartTableReady = '1';

                    const wrapper = table.closest('.table-responsive') ?? table.parentElement;
                    const headerLabels = Array.from(table.querySelectorAll('thead th')).map(function(header) {
                        return header.textContent.trim();
                    });

                    table.querySelectorAll('tbody tr').forEach(function(row) {
                        Array.from(row.children).forEach(function(cell, index) {
                            if (!cell.getAttribute('data-label')) {
                                cell.setAttribute('data-label', headerLabels[index] ?? 'Valeur');
                            }
                        });
                    });

                    if (!wrapper || !wrapper.parentNode) {
                        return;
                    }

                    wrapper.classList.add('smart-table-wrapper');

                    const toolbar = document.createElement('div');
                    toolbar.className = 'smart-table-toolbar';

                    const hint = document.createElement('span');
                    hint.className = 'small text-muted';
                    hint.textContent = 'Recherche rapide';

                    const input = document.createElement('input');
                    input.type = 'search';
                    input.className = 'form-control';
                    input.placeholder = 'Rechercher dans ' + (table.dataset.tableTitle ?? 'ce tableau') + '...';

                    toolbar.appendChild(hint);
                    toolbar.appendChild(input);
                    wrapper.parentNode.insertBefore(toolbar, wrapper);

                    const emptyState = document.createElement('div');
                    emptyState.className = 'smart-table-empty-state';
                    emptyState.textContent = 'Aucun resultat pour cette recherche.';
                    wrapper.parentNode.insertBefore(emptyState, wrapper.nextSibling);

                    const rows = Array.from(table.querySelectorAll('tbody tr'));

                    input.addEventListener('input', function() {
                        const query = input.value.toLowerCase().trim();
                        let visibleRows = 0;

                        rows.forEach(function(row) {
                            const placeholderRow = row.querySelector('td[colspan]');

                            if (placeholderRow) {
                                row.style.display = query === '' ? '' : 'none';
                                return;
                            }

                            const matches = row.textContent.toLowerCase().includes(query);
                            row.style.display = matches ? '' : 'none';

                            if (matches) {
                                visibleRows += 1;
                            }
                        });

                        emptyState.style.display = query !== '' && visibleRows === 0 ? 'block' : 'none';
                    });
                });
            });
        </script>
        <script>
            // Personnalisation des graphiques pour Toolzy
            document.addEventListener('DOMContentLoaded', function() {
                if ($("#visit-sale-chart").length) {
                    Chart.defaults.global.legend.labels.usePointStyle = true;
                    var ctx = document.getElementById('visit-sale-chart').getContext("2d");

                    var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 181);
                    gradientStroke1.addColorStop(0, '#4776E6');
                    gradientStroke1.addColorStop(1, '#8E54E9');

                    var gradientStroke2 = ctx.createLinearGradient(0, 0, 0, 360);
                    gradientStroke2.addColorStop(0, '#FF8008');
                    gradientStroke2.addColorStop(1, '#FFC837');

                    var myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil'],
                            datasets: [{
                                    label: 'Ordinateurs',
                                    borderColor: gradientStroke1,
                                    backgroundColor: gradientStroke1,
                                    pointBackgroundColor: gradientStroke1,
                                    pointBorderColor: 'rgba(255,255,255,0)',
                                    pointHoverBackgroundColor: gradientStroke1,
                                    pointBorderWidth: 0,
                                    pointHoverRadius: 0,
                                    pointHoverBorderWidth: 0,
                                    pointRadius: 0,
                                    fill: true,
                                    borderWidth: 2,
                                    data: [65, 59, 80, 81, 56, 55, 40]
                                },
                                {
                                    label: 'Autres équipements',
                                    borderColor: gradientStroke2,
                                    backgroundColor: gradientStroke2,
                                    pointBackgroundColor: gradientStroke2,
                                    pointBorderColor: 'rgba(255,255,255,0)',
                                    pointHoverBackgroundColor: gradientStroke2,
                                    pointBorderWidth: 0,
                                    pointHoverRadius: 0,
                                    pointHoverBorderWidth: 0,
                                    pointRadius: 0,
                                    fill: true,
                                    borderWidth: 2,
                                    data: [30, 40, 35, 50, 49, 60, 70]
                                }
                            ]
                        },
                        options: {
                            legend: {
                                display: false,
                            },
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        maxTicksLimit: 5,
                                        stepSize: 20,
                                        max: 100
                                    },
                                    gridLines: {
                                        borderDash: [3, 3],
                                        drawBorder: false,
                                        color: 'rgba(0,0,0,0.1)',
                                        zeroLineColor: 'rgba(0,0,0,0)',
                                    }
                                }],
                                xAxes: [{
                                    gridLines: {
                                        display: false,
                                        drawBorder: false,
                                        color: 'rgba(0,0,0,0.1)',
                                        zeroLineColor: 'rgba(0,0,0,0)',
                                    },
                                    ticks: {
                                        padding: 20,
                                        fontColor: "#9c9fa6",
                                        autoSkip: true,
                                    }
                                }]
                            }
                        }
                    });
                    document.getElementById('visit-sale-chart-legend').innerHTML = myChart.generateLegend();
                }

                if ($("#traffic-chart").length) {
                    var ctx = document.getElementById('traffic-chart').getContext("2d");
                    var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 181);
                    gradientStroke1.addColorStop(0, '#4776E6');
                    gradientStroke1.addColorStop(1, '#8E54E9');

                    var gradientStroke2 = ctx.createLinearGradient(0, 0, 0, 50);
                    gradientStroke2.addColorStop(0, '#FF8008');
                    gradientStroke2.addColorStop(1, '#FFC837');

                    var gradientStroke3 = ctx.createLinearGradient(0, 0, 0, 50);
                    gradientStroke3.addColorStop(0, '#11998e');
                    gradientStroke3.addColorStop(1, '#38ef7d');

                    var gradientStroke4 = ctx.createLinearGradient(0, 0, 0, 50);
                    gradientStroke4.addColorStop(0, '#f5515f');
                    gradientStroke4.addColorStop(1, '#ff9966');

                    var myChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            datasets: [{
                                data: [40, 25, 20, 15],
                                backgroundColor: [
                                    gradientStroke1,
                                    gradientStroke2,
                                    gradientStroke3,
                                    gradientStroke4
                                ],
                                hoverBackgroundColor: [
                                    gradientStroke1,
                                    gradientStroke2,
                                    gradientStroke3,
                                    gradientStroke4
                                ],
                                borderColor: [
                                    gradientStroke1,
                                    gradientStroke2,
                                    gradientStroke3,
                                    gradientStroke4
                                ],
                                legendColor: [
                                    gradientStroke1,
                                    gradientStroke2,
                                    gradientStroke3,
                                    gradientStroke4
                                ]
                            }],
                            labels: [
                                'Ordinateurs',
                                'Téléphones',
                                'Imprimantes',
                                'Autres'
                            ]
                        },
                        options: {
                            responsive: true,
                            cutoutPercentage: 70,
                            legend: false,
                            animation: {
                                animateScale: true,
                                animateRotate: true
                            }
                        }
                    });
                    document.getElementById('traffic-chart-legend').innerHTML = myChart.generateLegend();
                }
            });
        </script>
        <script>
            // Fonction pour afficher la popup d'image
            function showImagePopup(imageSrc, imageTitle) {
                const popup = document.getElementById('imagePopup');
                const popupImage = document.getElementById('popupImage');
                const popupTitle = document.getElementById('popupImageTitle');

                popupImage.src = imageSrc;
                popupTitle.textContent = imageTitle;
                popup.style.display = 'flex';

                // Empêcher le défilement de la page lorsque la popup est ouverte
                document.body.style.overflow = 'hidden';
            }

            // Fonction pour fermer la popup d'image
            function closeImagePopup() {
                const popup = document.getElementById('imagePopup');
                popup.style.display = 'none';

                // Rétablir le défilement de la page
                document.body.style.overflow = 'auto';
            }

            // Fermer la popup si on clique en dehors de l'image
            document.getElementById('imagePopup').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeImagePopup();
                }
            });

            // Fermer la popup avec la touche ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeImagePopup();
                }
            });

            // Script pour gérer les actions sur les équipements
            document.addEventListener('DOMContentLoaded', function() {
                // Gestion des clics sur les icônes d'action
                document.querySelectorAll('.edit-icon').forEach(icon => {
                    icon.addEventListener('click', function() {
                        // Logique pour modifier l'équipement
                        console.log('Modifier équipement:', this.closest('tr').querySelector('td')
                            .textContent.trim());
                    });
                });


                // Gestion de la recherche dans la navbar
                const searchInput = document.getElementById('navbar-search');
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = document.querySelectorAll('tbody tr');

                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            });
        </script>

        <!-- End custom js for this page-->
</body>

</html>
