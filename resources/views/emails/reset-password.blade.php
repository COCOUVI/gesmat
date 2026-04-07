<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body style="font-family: 'Segoe UI', sans-serif; background: #f4f4f4; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
        
        <!-- En-tête -->
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="font-size: 32px;">🛠️</div>
            <h1 style="color: #1e293b; margin: 10px 0 5px;">J-MAT</h1>
            <h2 style="color: #2563eb; font-size: 20px;">Réinitialisation de mot de passe</h2>
        </div>

        <!-- Message principal -->
        <p style="font-size: 16px; color: #333;">Bonjour {{ $user->prenom ?? $user->name ?? '' }},</p>

        <p style="font-size: 16px; color: #333;">
            Nous avons reçu une demande de réinitialisation de votre mot de passe.
            Cliquez sur le bouton ci-dessous pour le réinitialiser :
        </p>

        <!-- Bouton (responsive) -->
        <table role="presentation" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 30px auto;">
            <tr>
                <td align="center" bgcolor="#2563eb" style="border-radius: 6px;">
                    <a href="{{ $url }}" 
                       style="display: inline-block; padding: 14px 25px; font-size: 16px; font-weight: bold;
                              color: #ffffff; text-decoration: none; border-radius: 6px;">
                        Réinitialiser mon mot de passe
                    </a>
                </td>
            </tr>
        </table>

        <!-- Infos supplémentaires -->
        <p style="font-size: 14px; color: #777;">
            Ce lien expirera dans 60 minutes.
        </p>
        <p style="font-size: 14px; color: #777;">
            Si vous n’avez pas fait cette demande, vous pouvez ignorer cet email.
        </p>

        <!-- Lien brut -->
        <hr style="margin: 40px 0; border: none; border-top: 1px solid #eee;">
        <p style="font-size: 13px; color: #999;">
            Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :<br>
            <a href="{{ $url }}" style="color: #2563eb;">{{ $url }}</a>
        </p>

        <!-- Footer -->
        <p style="text-align: center; font-size: 12px; color: #aaa; margin-top: 40px;">
            © {{ date('Y') }} JASPETools. Tous droits réservés.
        </p>
    </div>
</body>
</html>
