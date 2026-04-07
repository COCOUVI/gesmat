<style>
    :root {
        --toolzy-primary:rgb(1, 120, 150);
        --toolzy-secondary:rgb(1, 120, 150);
        --toolzy-success: #38ef7d;
        --toolzy-warning:rgb(51, 179, 26);
        --toolzy-danger: #f5515f;
        --toolzy-light: #f8f9fa;
        --toolzy-dark: #343a40;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f7fa;
    }

    .bg-gradient-toolzy-primary {
        background: linear-gradient(to right, var(--toolzy-primary), var(--toolzy-secondary));
    }

    .bg-gradient-toolzy-secondary {
        background: linear-gradient(to right, #FF8008, var(--toolzy-warning));
    }

    .bg-gradient-toolzy-success {
        background: linear-gradient(to right, #11998e, var(--toolzy-success));
    }

    .bg-gradient-toolzy-danger {
        background: linear-gradient(to right, var(--toolzy-danger), #ff9966);
    }

    .text-toolzy {
        color: var(--toolzy-primary);
    }

    .badge-toolzy {
        background: linear-gradient(to right, var(--toolzy-primary), var(--toolzy-secondary));
        color: white;
    }

    .btn-toolzy {
        background: linear-gradient(to right, var(--toolzy-primary), var(--toolzy-secondary));
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .btn-toolzy:hover {
        background: linear-gradient(to right, var(--toolzy-secondary), var(--toolzy-primary));
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .toolzy-logo {
        font-size: 24px;
        font-weight: bold;
        color: var(--toolzy-primary);
    }

    .toolzy-logo i {
        margin-right: 8px;
    }




    .sidebar {
        background-color: rgba(255, 255, 255, 0.85); /* Fond clair et un peu transparent */
        padding: 15px;
        border-radius: 10px;
    }

   
    .sidebar .nav .nav-item.active>.nav-link {
        background: linear-gradient(to right, var(--toolzy-primary), var(--toolzy-secondary));
        border-radius: 6px;
    }

    .sidebar .nav .nav-item.active>.nav-link i,
    .sidebar .nav .nav-item.active>.nav-link .menu-title {
        color: #ffffff;
    }

    .sidebar .nav .nav-item .nav-link {
        border-radius: 6px;
        margin: 5px 15px;
        transition: all 0.3s;
    }

    .sidebar .nav .nav-item .nav-link:hover {
        background-color: rgba(71, 118, 230, 0.1);
    }

    .action-buttons .btn {
        margin-right: 5px;
    }

    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .card-img-holder {
        position: relative;
    }

    .card-img-absolute {
        position: absolute;
        top: 0;
        right: 0;
        opacity: 0.1;
    }

    .table {
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .table thead th {
        border-bottom: none;
        font-weight: 600;
        color: var(--toolzy-dark);
        padding: 12px;
    }

    .table tbody tr {
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        background-color: white;
    }

    .table tbody td {
        padding: 15px 12px;
        vertical-align: middle;
        border-top: none;
    }

    .table tbody tr td:first-child {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }

    .table tbody tr td:last-child {
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }

    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 500;
    }

    .badge-gradient-success {
        background: linear-gradient(to right, #11998e, var(--toolzy-success));
        color: white;
    }

    .badge-gradient-warning {
        background: linear-gradient(to right, #FF8008, var(--toolzy-warning));
        color: white;
    }

    .badge-gradient-danger {
        background: linear-gradient(to right, var(--toolzy-danger), #ff9966);
        color: white;
    }

    .page-header {
        background-color: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .page-title-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .footer {
        background-color: white;
        border-radius: 12px;
        padding: 15px;
        margin-top: 30px;
        box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
    }

    /* Styles pour les avatars avec initiales */
    .user-initials {
        width: 36px;
        height: 36px;
        border-radius: 6px;
        background: linear-gradient(to right, var(--toolzy-primary), var(--toolzy-secondary));
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: 10px;
    }

    .profile-initials {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(to right, var(--toolzy-primary), var(--toolzy-secondary));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: 10px;
    }

    .nav-profile {
        padding: 15px;
        margin-bottom: 15px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .nav-profile-text {
        display: flex;
        flex-direction: column;
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

    /* Amélioration des boutons d'action */
    .btn-outline-primary,
    .btn-outline-danger,
    .btn-outline-info,
    .btn-outline-warning,
    .btn-outline-success {
        border-radius: 6px;
        padding: 6px 10px;
        transition: all 0.3s;
    }

    .btn-outline-primary:hover,
    .btn-outline-danger:hover,
    .btn-outline-info:hover,
    .btn-outline-warning:hover,
    .btn-outline-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Styles spécifiques pour la page d'inventaire */
    .equipment-img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 6px;
        margin-right: 10px;
        cursor: pointer;
        transition: transform 0.3s;
    }

    .equipment-img:hover {
        transform: scale(1.05);
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-available {
        background-color: rgba(56, 239, 125, 0.1);
        color: #11998e;
    }

    .status-assigned {
        background-color: rgba(255, 200, 55, 0.1);
        color: #FF8008;
    }

    .status-maintenance {
        background-color: rgba(245, 81, 95, 0.1);
        color: #f5515f;
    }

    .action-icon {
        font-size: 1.2rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .action-icon:hover {
        transform: scale(1.1);
    }

    .edit-icon {
        color: var(--toolzy-primary);
    }

    .delete-icon {
        color: var(--toolzy-danger);
    }

    /* Styles pour la popup d'image */
    .image-popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .image-popup-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
    }

    .image-popup-content img {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .close-popup {
        position: absolute;
        top: -40px;
        right: -10px;
        color: white;
        font-size: 30px;
        cursor: pointer;
        background: var(--toolzy-danger);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    .close-popup:hover {
        background: #d43f3a;
        transform: rotate(90deg);
    }

    .image-popup-title {
        color: white;
        text-align: center;
        margin-top: 15px;
        font-size: 1.2rem;
    }

    .form-card {
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border: none;
        margin-bottom: 30px;
    }

    .form-header {
        background: linear-gradient(to right, var(--toolzy-primary), var(--toolzy-secondary));
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 20px 25px;
    }

    .form-body {
        padding: 25px;
        background-color: #fff;
        border-radius: 0 0 12px 12px;
    }

    .form-icon {
        background: rgba(255, 255, 255, 0.2);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        padding: 12px 15px;
        border: 1px solid #e1e5eb;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--toolzy-primary);
        box-shadow: 0 0 0 0.25rem rgba(71, 118, 230, 0.25);
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 8px;
        color: #495057;
    }

    .required-label:after {
        content: " *";
        color: var(--toolzy-danger);
    }

    .form-footer {
        background-color: #f8f9fa;
        padding: 20px 25px;
        border-radius: 0 0 12px 12px;
        border-top: 1px solid #e1e5eb;
    }

    .btn-submit {
        background: linear-gradient(to right, var(--toolzy-primary), var(--toolzy-secondary));
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .btn-reset {
        background: #f8f9fa;
        color: #495057;
        border: 1px solid #e1e5eb;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-reset:hover {
        background: #e9ecef;
    }

    /* Toast position and style */
    .toast-container {
        z-index: 1050;
    }

    /* Ajout des styles pour les champs de mot de passe */
    .password-input-group {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #6c757d;
        z-index: 10;
    }

    .password-toggle:focus {
        outline: none;
    }

    .alert-green {
        background: rgb(121, 168, 121);
    }

    .image-popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .image-popup-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
    }

    .image-popup-content img {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .close-popup {
        position: absolute;
        top: -40px;
        right: -10px;
        color: white;
        font-size: 30px;
        cursor: pointer;
        background: var(--toolzy-danger);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    .close-popup:hover {
        background: #d43f3a;
        transform: rotate(90deg);
    }

    .image-popup-title {
        color: white;
        text-align: center;
        margin-top: 15px;
        font-size: 1.2rem;
    }
</style>
