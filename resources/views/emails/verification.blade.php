<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vérification email</title>
</head>
<body style="font-family: sans-serif; background-color: #f8f9fa; padding: 40px;">
    <div style="max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px;">
        <div style="text-align: center;">
            <img src="https://ton-site.com/logo.png" alt="TOOLZY" style="height: 60px; margin-bottom: 20px;">
            <h2 style="color: #1e293b;">Bienvenue sur J-MAT</h2>
        </div>

        <p style="font-size: 16px; color: #333;">
            Merci de vous être inscrit ! Cliquez sur le bouton ci-dessous pour vérifier votre adresse email.
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" style="background: #2563eb; color: white; padding: 15px 25px; text-decoration: none; border-radius: 6px;">
                Vérifier mon adresse
            </a>
        </div>

        <p style="font-size: 14px; color: #555;">
            Si vous n'avez pas créé de compte, vous pouvez ignorer cet e-mail.
        </p>

        <p style="font-size: 14px; color: #999;">
            — L'équipe J-MAT
        </p>
    </div>
</body>
</html>
