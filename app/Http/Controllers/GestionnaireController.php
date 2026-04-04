<?php

declare(strict_types=1);

namespace App\Http\Controllers;

/**
 * Contrôleur de compatibilité legacy.
 *
 * Les workflows gestionnaire réellement actifs sont désormais répartis dans
 * les contrôleurs spécialisés et les actions partagées utilisées par
 * l'interface admin/gestionnaire.
 *
 * Cette classe est conservée uniquement pour éviter une rupture si un ancien
 * import local existe encore pendant la transition.
 */
final class GestionnaireController extends Controller {}
