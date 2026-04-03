# Cadrage Stock, Pannes, Demandes et Retours

## Objectif

Ce document sert de référence de travail pour le chantier de gestion de stock dans l'application.

Il rassemble :

- les règles métier discutées jusqu'ici ;
- les formules de calcul à retenir ;
- les scénarios à couvrir ;
- l'état actuel du code ;
- les écarts entre le comportement attendu et l'implémentation actuelle ;
- les points à corriger ou à implémenter ensuite.

Ce document doit devenir la base de décision avant toute nouvelle implémentation sur les demandes, affectations, pannes, retours et mouvements de stock.

---

## Principe métier central

Le point névralgique du système est la table `affectations`.

Une affectation représente une sortie réelle d'équipements vers un utilisateur, qu'elle provienne :

- d'une affectation directe ;
- d'une demande d'équipement acceptée.

Une panne doit être rattachée à une `affectation_id` quand elle concerne un équipement déjà affecté à un employé.

Cela permet de savoir :

- quelle affectation est concernée ;
- combien d'unités de cette affectation sont en panne ;
- combien d'unités restent saines dans cette même affectation ;
- comment calculer l'état réel du stock sans ambiguïté.

---

## Tables et rôles métier

## Tables réellement impactées par le processus

Oui, on peut dire que les tables principales réellement impactées par ce chantier sont :
<!--  -->
- `equipements`
- `demandes`
- `affectations`
- `pannes`

Mais il faut ajouter une nuance importante :

- `equipement_demandés` est aussi impactée fonctionnellement, car elle contient le détail de ce qui a été demandé ;
- `bons` est impactée par le workflow, mais pas par le calcul du stock.

Donc on peut distinguer trois niveaux :

### 1. Tables cœur de calcul du stock

Ce sont celles qui déterminent l'état réel du stock et des mouvements :

- `equipements`
- `affectations`
- `pannes`

### 2. Tables cœur de workflow de demande

Ce sont celles qui déterminent ce qui a été demandé, ce qui a été servi, et le lien entre besoin initial et affectation réelle :

- `demandes`
- `equipement_demandés`
- `affectations`

### 3. Tables périphériques au stock

Elles sont liées au processus, mais ne doivent pas porter la logique de calcul du stock :

- `bons`

Conclusion pratique :

- si on parle strictement de calcul de stock, les tables centrales sont `equipements`, `affectations` et `pannes` ;
- si on parle du workflow complet demande -> service -> affectation -> panne -> retour, alors il faut inclure `demandes` et `equipement_demandés` ;
- `bons` reste une conséquence documentaire du processus, pas une source de vérité pour le stock.

### `equipements`

Contient le stock physique total d'un type d'équipement.

Le champ `quantite` doit être compris comme :

- le stock total physique ;
- pas le stock disponible ;
- pas le stock restant après affectation.

### `demandes`

Contient les demandes faites par les employés.

Une demande exprime un besoin.

Une demande ne représente pas une sortie réelle tant qu'elle n'est pas servie.

### `equipement_demandés`

Contient le détail des équipements demandés et les quantités demandées.

### `affectations`

Contient les sorties réelles vers les utilisateurs.

Une affectation doit pouvoir représenter :

- une affectation directe ;
- une affectation issue d'une demande ;
- une affectation partiellement retournée ;
- une affectation avec une partie saine et une partie en panne.

À terme, c'est cette table qui doit représenter ce qui est réellement chez l'employé.

Champs et informations métier clés dans `affectations` :

- `equipement_id`
- `user_id`
- `demande_id` nullable pour distinguer demande acceptée et affectation directe
- `quantite_affectee`
- `quantite_retournee`
- `date_retour` nullable
- `statut`
- `created_by`

### `pannes`

Contient les quantités en panne.

Quand la panne vient d'un employé, elle doit pointer vers l'affectation concernée.

À terme, il faudra aussi couvrir les pannes de stock interne non affecté.

---

## Règles de calcul validées

## 1. Quantité affectée

La quantité affectée est la quantité réellement sortie vers les employés et non encore revenue dans le stock interne.

Exemple :

- stock total imprimante = 10
- affectation A = 2
- affectation B = 1

Alors :

- quantité affectée totale = 3

## 2. Quantité en panne

La quantité en panne est une information d'état.

Elle ne doit pas être soustraite une deuxième fois du stock disponible si elle se trouve déjà dans une affectation active.

Exemple :

- stock total = 10
- quantité affectée = 3
- parmi ces 3, une unité est en panne

Alors :

- quantité disponible = 7
- pas 6

Pourquoi :

- l'unité en panne fait déjà partie des 3 unités sorties ;
- elle est déjà comptée dans la quantité affectée ;
- la soustraire encore ferait un double comptage.

## 3. Quantité disponible

Règle métier retenue :

`quantité disponible = stock total - quantité affectée active - quantité en panne interne non affectée`

Cela veut dire :

- une panne sur un équipement encore affecté à un employé ne réduit pas une seconde fois le disponible ;
- une panne sur un équipement revenu au stock mais non réparé bloque le disponible ;
- une panne sur un équipement interne jamais affecté devra aussi bloquer le disponible.

## 4. Cas d'un retour d'équipement en panne

Si un équipement en panne est retourné mais n'est pas encore résolu :

- il ne fait plus partie de la quantité affectée active ;
- mais il ne redevient pas disponible ;
- il doit alors être compté comme panne interne non résolue.

Exemple :

- stock total = 10
- avant retour : 3 affectées, dont 1 en panne
- disponible = 7

Après retour de l'unité en panne non réparée :

- 2 affectées
- 1 panne interne non résolue
- disponible = 7

Après résolution de la panne :

- 2 affectées
- 0 panne interne non résolue
- disponible = 8

---

## Découpage conceptuel conseillé du stock

Pour éviter les ambiguïtés, on peut raisonner avec les sous-ensembles suivants :

- `stock total`
- `stock affecté sain`
- `stock affecté en panne`
- `stock interne sain disponible`
- `stock interne en panne`

Conservation :

`stock total = stock affecté sain + stock affecté en panne + stock interne sain disponible + stock interne en panne`

Ce découpage couvre correctement les cas de figure métier.

---

## Cas métier à couvrir

## A. Déjà évoqués et validés

### 0. Affectation directe par admin ou gestionnaire

Ce processus suit les mêmes règles de stock que les demandes.

Règles à retenir :

- l'affectation cible uniquement un employé ;
- on vérifie toujours le stock disponible réel ;
- la date de retour est optionnelle ;
- une affectation directe crée une vraie ligne dans `affectations` ;
- un bon de sortie est généré ;
- l'affectation doit être visible côté admin/gestionnaire et côté employé avec ses quantités réelles.

Décisions métier désormais fixées :

- si le même équipement est saisi plusieurs fois avec la même date de retour, les lignes sont fusionnées dans une seule affectation ;
- si le même équipement est saisi plusieurs fois avec des dates de retour différentes, les lignes restent séparées ;
- une affectation n'est annulable que si aucune quantité n'a été retournée et si aucun historique de panne n'y est lié.

### 1. Deux affectations du même équipement pour le même employé

Exemple :

- 2 imprimantes via demande acceptée ;
- 1 imprimante via affectation directe.

Le système doit rester piloté par `affectation_id`.

Pourquoi :

- une panne ne doit pas être déclarée au niveau global de l'équipement ;
- elle doit être déclarée sur la ligne d'affectation concernée ;
- on doit savoir quelle quantité de chaque affectation est saine ou en panne.

### 2. Retour partiel d'une affectation

Exemple :

- affectation de 3 imprimantes ;
- retour de 1 seule.

Ce cas doit être pris en charge.

Cela implique qu'une affectation ne peut plus être gérée uniquement en "tout ou rien".

### 3. Résolution partielle d'une panne

Exemple :

- 3 unités en panne sur une affectation ;
- 1 seulement est réparée.

Ce cas doit être pris en charge.

### 4. Demande partiellement servie

Exemple :

- 5 demandés ;
- 3 seulement affectés.

Ce cas doit être pris en charge.

Cela implique qu'il faut distinguer :

- ce qui a été demandé ;
- ce qui a été effectivement affecté ;
- ce qui reste non servi.

### 5. Panne sur stock interne non affecté

Cas à prévoir.

Exemple :

- un équipement est au magasin ;
- il tombe en panne sans être chez un employé.

Ce cas doit bloquer le stock disponible.

### 6. Annulation ou suppression d'une affectation déjà partiellement en panne

Cas à prendre en compte.

Il faudra définir précisément :

- si l'annulation est interdite tant qu'il y a des pannes ouvertes ;
- ou si elle convertit automatiquement les unités en panne en panne interne.

### 7. Remplacement d'une unité en panne par une unité saine

Cas à prévoir.

Exemple :

- 1 unité en panne chez un employé ;
- le magasin en fournit une autre saine.

Cela doit se traduire par un mouvement clair dans le stock et dans les affectations.

## B. Cas à expliciter davantage

### Retour d'une quantité en panne différente de la quantité saine

Ce cas signifie par exemple :

- affectation de 3 unités ;
- parmi elles, 1 est en panne ;
- l'utilisateur retourne seulement les 2 unités saines ;
- l'unité en panne reste encore chez lui ;

ou bien :

- il retourne l'unité en panne ;
- mais garde encore les unités saines.

Ce cas est important car il change la répartition entre :

- quantité affectée active ;
- panne affectée ;
- panne interne.

Il faudra donc permettre des retours partiels par état ou par quantité.

---

## Ce qui est déjà proposé ou commencé dans le code

## 1. Demande d'équipement côté employé

Présent.

Fichier principal :

- [EmployeController.php](/var/www/html/gesmat/app/Http/Controllers/EmployeController.php)

Fonctionnalité existante :

- l'employé soumet une demande ;
- la demande est enregistrée dans `demandes` ;
- les lignes sont enregistrées dans `equipement_demandés` ;
- les doublons de même équipement peuvent être consolidés au moment de l'enregistrement.

État :

- implémenté ;
- compatible avec le suivi du service partiel côté traitement.

## 2. Validation d'une demande

Présent.

Fichier principal :

- [AdminController.php](/var/www/html/gesmat/app/Http/Controllers/AdminController.php)

Fonctionnalité existante :

- vérification avant validation ;
- saisie explicite d'une quantité à servir pour chaque équipement demandé ;
- blocage si la quantité à servir dépasse le disponible réel ;
- blocage si la quantité à servir dépasse le restant de la demande ;
- création d'affectation issue de la demande ;
- génération d'un bon ;
- date de retour saisissable dans la modale.

État :

- implémenté en service partiel ;
- `demande_id` ajouté sur les affectations pour la traçabilité.

Décision actuelle d'implémentation :

- une demande partiellement servie reste en base avec le statut `en_attente` ;
- l'état métier `partiellement_servie` est dérivé à partir des affectations déjà créées ;
- une demande passe à `acceptee` seulement lorsque toutes les quantités demandées ont été servies.

## 3. Affectation directe

Présent.

Fichier principal :

- [AdminController.php](/var/www/html/gesmat/app/Http/Controllers/AdminController.php)

Fonctionnalité existante :

- affectation directe à un employé ;
- quantité ;
- date de retour ;
- bon généré.

État :

- implémenté ;
- pas encore compatible avec retour partiel ;
- pas encore modélisé finement pour distinguer quantité saine et quantité en panne au retour.

## 4. Signalement de panne par employé

Présent.

Fichier principal :

- [EmployeController.php](/var/www/html/gesmat/app/Http/Controllers/EmployeController.php)

Fonctionnalité existante :

- sélection par `affectation_id` ;
- contrôle sur la quantité restante signalable ;
- création d'une panne rattachée à l'affectation.

État :

- implémenté ;
- bon sens métier correct sur la traçabilité par affectation ;
- les quantités affichées côté employé sont maintenant alignées avec le restant réellement signalable sur l'affectation.

## 5. Résolution de panne

Présent.

Fichiers principaux :

- [AdminController.php](/var/www/html/gesmat/app/Http/Controllers/AdminController.php)
- [Panne.php](/var/www/html/gesmat/app/Models/Panne.php)

Fonctionnalité existante :

- résolution partielle d'une panne ;
- calcul de la quantité résoluble uniquement sur la part déjà revenue au stock interne ;
- passage automatique à `resolu` quand toute la panne est effectivement traitée.

État :

- implémenté en résolution partielle ;
- compatible avec les pannes internes ;
- compatible avec les pannes issues d'affectation quand la quantité concernée est revenue au stock.

## 6. Retour d'équipement

Présent.

Fichier principal :

- [AdminController.php](/var/www/html/gesmat/app/Http/Controllers/AdminController.php)

Fonctionnalité existante :

- retour d'une affectation ;
- retour partiel d'une quantité saine ;
- retour partiel d'une quantité en panne ;
- génération d'un PDF de retour ;
- suivi de la quantité déjà retournée sur l'affectation ;
- statut d'affectation mis à `retour_partiel` ou `retourné`.

État :

- implémenté en retour partiel ;
- la distinction sain / panne retournée est prise en compte ;
- le calcul de stock tient compte des pannes revenues au stock ;
- compatible avec la résolution partielle ;
- compatible avec le remplacement depuis une panne affectée.

---

## Ce qui est déjà implémenté mais doit être corrigé

## 1. Tableaux de stock globaux

Fichiers concernés :

- [listtools.blade.php](/var/www/html/gesmat/resources/views/admin/listtools.blade.php)
- [listtools.blade.php](/var/www/html/gesmat/resources/views/gestionnaire/tools/listtools.blade.php)

Constat :

- la formule officielle du stock est maintenant corrigée dans le modèle ;
- ces écrans doivent encore être relus pour vérifier que tout l'affichage métier est bien aligné sur cette formule unique.

## 2. GestionnaireController

Fichier concerné :

- [GestionnaireController.php](/var/www/html/gesmat/app/Http/Controllers/GestionnaireController.php)

Constat :

- ce contrôleur contient encore une logique parallèle et partiellement ancienne ;
- certaines méthodes continuent à manipuler directement `equipements.quantite` ;
- les routes actives du workflow principal passent aujourd'hui surtout par `AdminController` avec middleware `AdminOuGestionnaire`.

Conclusion :

- il faudra décider si ce contrôleur est encore utilisé pour ce périmètre ;
- sinon il faudra le réaligner ou le sortir du chemin critique.

---

## Ce qui reste à implémenter

## Priorité 1

### 1. Relire les tableaux de stock globaux

Il faut maintenant vérifier les écrans globaux de stock pour s'assurer qu'ils lisent tous la formule métier unique.

### 2. Réaligner ou écarter `GestionnaireController`

Le workflow principal passe aujourd'hui surtout par `AdminController`.

Il reste à décider :

- si `GestionnaireController` reste dans le périmètre actif ;
- ou s'il doit être réaligné pour ne plus porter une logique divergente.

### 3. Finaliser les cas avancés d'affectation directe

Les bases sont en place, mais il reste à arbitrer les cas comme :

- plusieurs lignes du même équipement dans une même opération ;
- lignes du même équipement avec dates de retour différentes ;
- stratégie de fusion ou de séparation des affectations créées.

## Priorité 2

### 4. Gérer la demande partiellement servie

Déjà commencé.

Il faut continuer à enregistrer :

- quantité demandée ;
- quantité servie ;
- quantité restante ;
- statut partiel.

### 5. Raffiner le remplacement d'une unité en panne

Le remplacement existe maintenant.

Il reste à préciser si l'on veut ensuite :

- générer un type de bon spécifique ;
- rattacher explicitement l'affectation de remplacement à la panne d'origine ;
- distinguer davantage le remplacement d'une simple affectation directe.

---

## Proposition d'approche pour la suite

Ordre recommandé :

1. figer le modèle métier définitif du stock ;
2. corriger le calcul du stock disponible ;
3. modéliser la panne interne ;
4. modéliser le retour partiel ;
5. modéliser la résolution partielle ;
6. modéliser le remplacement ;
7. gérer ensuite la demande partiellement servie ;
8. terminer par l'alignement des écrans et des contrôleurs secondaires.

---

## Décisions déjà stabilisées

- Le centre du workflow est `affectations`.
- Une panne d'employé doit être rattachée à `affectation_id`.
- Une demande acceptée crée une ou plusieurs affectations.
- Une demande peut, à terme, être servie partiellement.
- Le retour partiel doit être supporté.
- La résolution partielle de panne doit être supportée.
- Le stock interne en panne doit être pris en compte.
- Le remplacement d'une unité en panne doit être prévu.
- La quantité résolue est portée directement par `pannes.quantite_resolue`.
- Une panne interne est représentée par une ligne `pannes` avec `affectation_id = null`.
- Lors d'un remplacement, une nouvelle affectation est créée pour la quantité saine remise à l'employé.

---

## Questions produit encore à verrouiller

Voici les points qu'il faudra encore formaliser :

- Quel statut exact donner à une demande partiellement servie dans tous les écrans ?
- Une affectation peut-elle être fermée automatiquement s'il reste une panne ouverte qui lui est rattachée mais déjà revenue au stock ?
- Faut-il créer un bon dédié au remplacement plutôt que de réutiliser le bon de sortie ?
- Faut-il rattacher explicitement l'affectation de remplacement à la panne d'origine dans le schéma de données ?

---

## Conclusion

Le projet a déjà une bonne base :

- demandes ;
- affectations ;
- bons ;
- pannes par affectation ;
- retours ;
- résolution de pannes.

Mais il reste une étape importante de réalignement métier :

- réaligner les écrans globaux de stock ;
- nettoyer les contrôleurs parallèles encore anciens ;
- continuer à renforcer les cas partiels et la traçabilité documentaire.

La prochaine étape logique est donc :

- valider définitivement le modèle de calcul du stock ;
- puis corriger l'implémentation actuelle à partir de ce document.
