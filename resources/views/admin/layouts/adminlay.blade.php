<!DOCTYPE html>
<html lang="fr">

<head>
    <!-- Required meta tags -->
    <base href="/public">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>J-Tools - Administration</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/iconfonts/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- endinject -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="images/favicon.png" />
    @stack('styles')
    @include('admin.style')
</head>

<body>
    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        @include('admin.navbar')
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            @include('admin.sidebar')
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
                @include('admin.footer')
                <!-- main-panel ends -->
            </div>
            <!-- page-body-wrapper ends -->
        </div>
        <!-- container-scroller -->
        @stack('popups')
        <!-- plugins:js -->
        <script src="vendors/js/vendor.bundle.base.js"></script>
        <script src="vendors/js/vendor.bundle.addons.js"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>
        <!-- endinject -->
        <!-- Plugin js for this page-->
        <!-- End plugin js for this page-->
        <!-- inject:js -->
        <script src="js/off-canvas.js"></script>
        <script src="js/misc.js"></script>
        <script src="js/template.js"></script>
        <!-- endinject -->
        <!-- Custom js for this page-->
        @stack('scripts')
        <script src="js/dashboard.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                $('table.smart-data-table').each(function() {
                    const table = $(this);
                    const hasPlaceholderRow = table.find('tbody td[colspan]').length > 0;
                    const rowCount = table.find('tbody tr').length;

                    if (hasPlaceholderRow || rowCount === 0) {
                        return;
                    }

                    const headers = table.find('thead th').map(function() {
                        return $(this).text().trim().toLowerCase();
                    }).get();

                    const nonSortableTargets = headers.reduce(function(targets, header, index) {
                        if (['action', 'actions', 'photo', 'téléchargement', 'telechargement'].includes(header)) {
                            targets.push(index);
                        }

                        return targets;
                    }, []);

                    table.DataTable({
                        fixedHeader: true,
                        pageLength: 10,
                        lengthMenu: [10, 25, 50, 100],
                        order: [],
                        autoWidth: false,
                        scrollX: true,
                        columnDefs: nonSortableTargets.length > 0 ? [{
                            orderable: false,
                            targets: nonSortableTargets,
                        }] : [],
                        language: {
                            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json',
                        },
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
                        type: 'line',
                        data: {
                            labels: moisLabels, // ✅ données dynamiques injectées depuis homedash.blade.php
                            datasets: [{
                                label: 'Équipements ajoutés',
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
                                data: equipementsParMois // ✅ données dynamiques Laravel
                            }]
                        },
                        options: {
                            legend: {
                                display: true,
                            },
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        maxTicksLimit: 6,
                                        stepSize: 1
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
                const searchBtn = document.getElementById('navbar-search-btn');
                const searchInput = document.getElementById('navbar-search');

                searchBtn.addEventListener('click', function() {
                    const searchTerm = searchInput.value.toLowerCase().trim();
                    const rows = document.querySelectorAll('tbody tr');

                    rows.forEach(row => {
                        const equipementName = row.querySelector(
                            'td:nth-child(2)'); // 2ème colonne = nom de l'équipement
                        const text = equipementName ? equipementName.textContent.toLowerCase() : '';

                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });


            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');
                const hamburgerButton = document.querySelector('.navbar-toggler');

                hamburgerButton.addEventListener('click', function() {
                    if (window.innerWidth < 992) { // lg breakpoint Bootstrap
                        sidebar.classList.toggle('active');
                    }
                });
            });
        </script>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <!-- End custom js for this page-->
</body>

</html>
