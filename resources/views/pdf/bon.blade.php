<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Bon de Matériel</title>
    <style>
        @page {
            size: A5;
            margin: 15mm;
            /* marges physiques adaptées A5 */
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        .invoice-container {
            width: 100%;
            background-color: white;
            padding: 10px 15px;
            margin: 0 auto;
            border-radius: 10px;
            position: relative;
            z-index: 1;
        }

        .logo-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 250px;
            /* réduit pour A5 */
            opacity: 0.05;
            z-index: 0;
        }

        .invoice-header {
            margin-top: 0;
            /* supprimé le -90px */
        }

        .invoice-header table {
            width: 100%;
            table-layout: fixed;
        }

        .invoice-header td {
            vertical-align: top;
        }

        .invoice-logo img {
            max-width: 120px;
            height: auto;
        }

        .terms-text {
            font-size: 9px;
            text-align: right;
            color: #222;
            line-height: 1.4;
        }

        .invoice-title {
            font-weight: bold;
            text-align: center;
            font-size: 18px;
            color: #121314;
            margin: 20px 0;
            z-index: 2;
            position: relative;
        }

        .info-section p {
            margin: 3px 0;
            font-size: 12px;
        }

        .info-section strong {
            width: 100px;
            display: inline-block;
        }

        .motif-box {
            border: 1px solid #888;
            min-height: 80px;
            padding: 8px;
            background-color: #fafafa;
            margin-bottom: 30px;
            font-size: 11px;
        }

        .signature-table {
            width: 100%;
            margin-top: 30px;
            text-align: center;
        }

        .signature-table td {
            width: 50%;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 30px;
            width: 60%;
            margin-left: auto;
            margin-right: auto;
        }

        .signature-label {
            margin-top: 6px;
            font-size: 11px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <!-- Watermark -->
        <div class="logo-watermark">
            <img src="{{ public_path('images/test.jpeg') }}" alt="Filigrane">
        </div>

        <!-- En-tête -->
        <div class="invoice-header">
            <table>
                <tr>
                    <td class="invoice-logo">
                        <img src="{{ public_path('images/jaspe_logo_noir_web.png') }}" alt="Logo">
                    </td>
                    <td class="terms-text">
                        RÉSEAUX TÉLÉCOMS & INFORMATIQUE - FIBRE OPTIQUE<br>
                        MAINTENANCE - INTERPHONIE - VIDÉO SURVEILLANCE<br>
                        RB/ABC/19A12274 - 0201910605828<br>
                        (+229) 96 31 26 06 - contact@jaspetechnologies.com<br>
                        Calavi Bidossessi, 1ère von à droite après la Pharmacie Fleuve de Vie
                    </td>
                </tr>
            </table>
        </div>

        <!-- Titre -->
        <h1 class="invoice-title">BON {{ $type === 'entrée' ? "D'ENTREE" : 'DE SORTIE' }} DE MATÉRIEL</h1>

        <!-- Infos -->
        <div class="info-section">
            <p><strong>Date :</strong> {{ $date ?? now()->format('d/m/Y') }}</p>
            <p><strong>Numéro :</strong> {{ $numero_bon ?? '...' }}</p>
            <p><strong style="display:inline-block; min-width:130px;">Nom & Prénoms:</strong><span
                    style="display:inline-block;">{{ ($nom ?? 'Nom') . ' ' . ($prenom ?? '') }}</span></p>
        </div>

        <!-- ... autres sections du bon -->

        @php
            $afficheDateRetour = ! empty($equipements) && collect($equipements)->contains(
                fn ($eq) => ! empty($eq['date_retour'])
            );
        @endphp

        @if (!empty($equipements) && count($equipements) > 0)
            <h3 style="margin-top: 20px;">Détails des équipements affectés</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #aaa; padding: 5px; background: #eee;">N°</th>
                        <th style="border: 1px solid #aaa; padding: 5px; background: #eee;">Désignation</th>
                        <th style="border: 1px solid #aaa; padding: 5px; background: #eee;">Quantité</th>
                        @if ($afficheDateRetour)
                            <th style="border: 1px solid #aaa; padding: 5px; background: #eee;">Date de retour</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($equipements as $index => $eq)
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 4px;">{{ $index + 1 }}</td>
                            <td style="border: 1px solid #ccc; padding: 4px;">{{ $eq['nom'] ?? 'Inconnu' }}</td>
                            <td style="border: 1px solid #ccc; padding: 4px; text-align:center;">
                                {{ $eq['quantite'] ?? '' }}
                            </td>
                            @if ($afficheDateRetour)
                                <td style="border: 1px solid #ccc; padding: 4px; text-align:center;">
                                    {{ !empty($eq['date_retour']) ? \Illuminate\Support\Carbon::parse($eq['date_retour'])->format('d/m/Y') : '-' }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- ... suite du bon -->


        <!-- Motif -->
        <div class="info-section">
            <p><strong>Motif :</strong></p>
            <div class="motif-box">
                @php
                    $motifTexte = $motif ?? '';
                    $lignes = explode("\n", wordwrap($motifTexte, 90, "\n"));
                @endphp
                @foreach ($lignes as $ligne)
                    <div>{{ $ligne }}</div>
                @endforeach
            </div>
        </div>

        <!-- Signatures -->
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-line"></div>
                    <div class="signature-label">Responsable IT</div>
                </td>
                <td>
                    <div class="signature-line"></div>
                    <div class="signature-label">Agent</div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
