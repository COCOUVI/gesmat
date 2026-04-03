<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Attestation de retour de matériel</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .watermark {
            position: fixed;
            top: 30%;
            left: 15%;
            width: 70%;
            opacity: 0.06;
            z-index: -1;
        }

        .container {
            padding: 60px 70px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 40px;
        }

        .header img {
            width: 100px;
        }

        .header-title {
            font-size: 22px;
            font-weight: bold;
            text-align: right;
        }

        .content {
            margin-top: 20px;
            line-height: 1.7;
            font-size: 15px;
        }

        .content p {
            margin-bottom: 18px;
        }

        .footer {
            margin-top: 100px;
            text-align: right;
        }

        .footer .location-date {
            margin-bottom: 40px;
            font-style: italic;
        }

        .footer .sign-title {
            font-weight: bold;
        }

        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            width: 200px;
            margin-left: auto;
        }
    </style>
</head>
<body>
    <!-- Filigrane -->
    <img src="{{ public_path('images/jaspe_logo_noir_web.png') }}" class="watermark" alt="Filigrane Jaspe">

    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <div class="header-title">Attestation de Retour de Matériel</div>
        </div>

        <!-- Corps du document -->
        <div class="content">
            <p>Je soussigné <strong>{{ $nom }} {{ $prenom }}</strong>, atteste avoir retourné le matériel suivant appartenant à l'entreprise <strong>Jaspe Technologie</strong> :</p>

            <p>
                <strong>Désignation du matériel :</strong> {{ $equipement }}<br>
                <strong>Quantité totale retournée :</strong> {{ $quantite_retournee ?? 0 }}<br>
                <strong>Quantité saine retournée :</strong> {{ $quantite_saine_retournee ?? 0 }}<br>
                <strong>Quantité en panne retournée :</strong> {{ $quantite_panne_retournee ?? 0 }}<br>
                <strong>Date et heure de retour :</strong> {{ \Carbon\Carbon::parse($date)->format('d/m/Y à H\hi') }}
            </p>

            <p>Le présent document fait foi du retour effectif du matériel aux services compétents de l’entreprise.</p>
        </div>

        <!-- Signature -->
        <div class="footer">
            <div class="location-date">Fait à Cotonou, le {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</div>
            <div class="sign-title">Responsable IT</div>
            <div class="signature-line"></div>
        </div>
    </div>
</body>
</html>
