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

      .smart-table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        flex-wrap: wrap;
      }

      .smart-table-toolbar .form-control {
        max-width: 320px;
      }

      .smart-table-empty-state {
        display: none;
        padding: 16px;
        border: 1px dashed rgba(0, 0, 0, 0.12);
        border-radius: 10px;
        color: #6c757d;
        text-align: center;
        background: #fff;
        margin-top: 12px;
      }

      @media (max-width: 767.98px) {
        .smart-table-wrapper {
          overflow: visible;
        }

        .smart-data-table thead {
          display: none;
        }

        .smart-data-table,
        .smart-data-table tbody,
        .smart-data-table tr,
        .smart-data-table td {
          display: block;
          width: 100%;
        }

        .smart-data-table tbody tr {
          margin-bottom: 14px;
          padding: 10px 12px;
          background: #fff;
          border-radius: 12px;
          box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
        }

        .smart-data-table tbody td {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          gap: 12px;
          text-align: right;
          padding: 10px 0;
          white-space: normal;
        }

        .smart-data-table tbody td::before {
          content: attr(data-label);
          font-weight: 700;
          color: var(--dark);
          text-align: left;
          flex: 0 0 45%;
        }
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
    <!-- End custom js for this page-->
  </body>
</html>
