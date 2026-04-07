<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>J-MAT | Gestion de Matériel - Jaspe Technologie</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     @yield("style")
</head>
<body>
    <!-- Barre de navigation avec correction -->
     @include('layouts.navigation')
    <!-- Section Hero avec nouvelle image significative -->
      @yield('content')
    <!-- Section CTA -->
   
    
    <!-- Pied de page -->
      @include("layouts.footer")

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation pour les badges
        document.addEventListener('DOMContentLoaded', function() {
            const badges = document.querySelectorAll('.stats-badge, .feature-badge');
            badges.forEach(badge => {
                badge.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    badge.style.transition = 'transform 0.5s ease';
                    badge.style.transform = 'scale(1)';
                }, 300);
            });
            
            // Animation pour les cartes de fonctionnalités
            const featureCards = document.querySelectorAll('.feature-card');
            featureCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.5s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });
        });
    </script>
    @stack("scripts")
</body>
</html>