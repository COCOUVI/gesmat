<!DOCTYPE html>
<html lang="fr">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>J-Tools | Tableau de bord Employé</title>
    <!-- base:css -->
    <link rel="stylesheet" href="/vendors1/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="/vendors1/base/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="/css1/style.css">
    <!-- endinject -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <link rel="shortcut icon" href="/images1/favicon.png" />
    <style>
      :root {
        --primary: #2563eb;
        --secondary: #1e40af;
        --accent: #f59e0b;
        --dark: #1e293b;
      }
      .navbar-brand .logo-text {
        font-weight: 700;
        color: var(--dark);
        font-size: 1.5rem;
      }
      .btn-primary {
        background: var(--primary);
        border: none;
      }
      .btn-primary:hover {
        background: var(--secondary);
      }
      .btn-accent {
        background: var(--accent);
        color: white;
      }
      .badge-success {
        background-color: #10b981;
      }
      .badge-warning {
        background-color: #f59e0b;
      }
      .badge-info {
        background-color: #3b82f6;
      }
      .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      }
      .top-navbar {
        background: white !important;
      }
      .bottom-navbar {
        background: white !important;
      }
      .nav-link.active {
        color: var(--primary) !important;
        font-weight: 600;
      }

      .dataTables_wrapper .dataTables_filter input,
      .dataTables_wrapper .dataTables_length select {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 0.375rem 0.75rem;
      }

      .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        margin: 0 2px;
      }

      .dataTables_wrapper .dataTables_info,
      .dataTables_wrapper .dataTables_length,
      .dataTables_wrapper .dataTables_filter {
        margin-bottom: 12px;
      }

      .choices__inner {
        min-height: 38px;
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        background-color: #fff;
        padding: 0.4375rem 0.75rem;
      }

      .choices__list--dropdown,
      .choices__list[aria-expanded] {
        z-index: 1085;
      }
    </style>
    @stack('styles')
  </head>
  <body>
    <div class="container-scroller">
      <!-- partial:partials/_horizontal-navbar.html -->
       @include("employee.partials.navbar")
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
            @yield('content')
      
      </div>
       <!-- partial:partials/_footer.html -->
        @include("employee.partials.footer")
    <!-- partial -->
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- base:js -->
    @yield('scripts')
    <script src="/vendors1/base/vendor.bundle.base.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page-->
    <!-- End plugin js for this page-->
    <!-- inject:js -->
    <script src="/js1/template.js"></script>
    <!-- endinject -->
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <!-- plugin js for this page -->
    <!-- End plugin js for this page -->
    <script src="/vendors1/chart.js/Chart.min.js"></script>
    <script src="/vendors1/progressbar.js/progressbar.min.js"></script>
    <script src="/vendors1/chartjs-plugin-datalabels/chartjs-plugin-datalabels.js"></script>
    <script src="/vendors1/justgage/raphael-2.1.4.min.js"></script>
    <script src="/vendors1/justgage/justgage.js"></script>
    <script src="/js1/jquery.cookie.js" type="text/javascript"></script>
    <!-- Custom js for this page-->
    <script src="/js1/dashboard.js"></script>
    <script>
      window.initEnhancedSelects = function(scope = document) {
        const root = scope instanceof Element || scope instanceof Document ? scope : document;
        const selects = root.querySelectorAll('select');

        selects.forEach(function(select) {
          if (select.dataset.choiceInitialized === 'true') {
            return;
          }

          if (select.classList.contains('choices__input')) {
            return;
          }

          if (select.dataset.enhanceSelect === 'false') {
            return;
          }

          const optionCount = select.querySelectorAll('option').length;
          const emptyOption = Array.from(select.options).find(function(option) {
            return option.value === '';
          });

          new Choices(select, {
            shouldSort: false,
            itemSelectText: '',
            searchEnabled: true,
            removeItemButton: select.multiple,
            allowHTML: false,
            noResultsText: 'Aucun résultat',
            noChoicesText: 'Aucun choix disponible',
            searchPlaceholderValue: 'Rechercher...',
            placeholder: Boolean(emptyOption),
            placeholderValue: emptyOption ? emptyOption.textContent.trim() : '',
          });

          select.dataset.choiceInitialized = 'true';
        });
      };

      document.addEventListener('DOMContentLoaded', function() {
        window.initEnhancedSelects();
      });
    </script>
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
            fixedHeader: table.data('fixed-header') !== false,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [],
            autoWidth: false,
            scrollX: table.data('scroll-x') !== false,
            columnDefs: nonSortableTargets.length > 0 ? [{
              orderable: false,
              targets: nonSortableTargets,
            }] : [],
            language: {
              url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json',
            },
          });

          if (window.initEnhancedSelects) {
            window.initEnhancedSelects(table.closest('.dataTables_wrapper').get(0) || document);
          }
        });
      });
    </script>
    <!-- End custom js for this page-->
  </body>
</html>
