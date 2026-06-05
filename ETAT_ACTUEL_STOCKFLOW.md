# Etat actuel de StockFlow

Document de reference du projet au 3 juin 2026.

StockFlow est une application Laravel mono-entreprise de gestion de stock. Elle gere un catalogue produits, les entrees et sorties de stock, les inventaires physiques, les alertes de seuil critique, les previsions de rupture, les rapports exportables et les permissions par roles.

L'application n'est pas un SaaS multi-tenant :
- une seule societe ;
- pas de separation par entreprise ;
- pas d'abonnement ;
- pas de facturation ;
- pas de super-admin SaaS.

## 1. Etat fonctionnel actuel

L'application dispose aujourd'hui des modules suivants :

- Dashboard de pilotage.
- Produits.
- Categories.
- Fournisseurs.
- Stock : entree, sortie, ajustement.
- Inventaires physiques.
- Alertes de stock.
- Previsions de rupture.
- Rapports PDF / Excel.
- Utilisateurs.
- Roles et permissions.
- Profil utilisateur.

L'interface utilise une sidebar sombre en violet (`violet-950`) avec groupes repliables :
- Pilotage : Dashboard, Alertes, Previsions, Rapports.
- Stock : Produits, Inventaires.
- Referentiel : Categories, Fournisseurs.
- Administration : Utilisateurs, Roles.

Les couleurs logiques sont conservees :
- vert : succes, entree, resolu, validation, Excel ;
- rouge / rose : sortie, rupture, critique ;
- orange / ambre : risque eleve ou modere, seuil ;
- bleu ciel : ajustement ;
- violet : navigation, boutons principaux et actions UI.

## 2. Stack technique

Backend :
- PHP 8.2+.
- Laravel 12.
- Laravel Breeze.
- Livewire 3 et Volt pour l'authentification / composants Livewire.
- Spatie Laravel Permission pour les roles et permissions.
- DomPDF pour les exports PDF.
- Maatwebsite Excel pour les exports Excel.

Frontend :
- Blade.
- Tailwind CSS.
- Vite.
- Alpine.js via le stack Laravel / Livewire.
- ApexCharts pour les graphes.

Base de donnees locale :
- SQLite.
- Fichier : `database/database.sqlite`.

## 3. Structure generale du projet

Les dossiers importants sont :

- `routes/web.php` : routes web, middlewares, permissions.
- `app/Http/Controllers` : controleurs HTTP.
- `app/Http/Requests` : validations de formulaires metier.
- `app/Services` : logique metier centrale.
- `app/Models` : modeles Eloquent et relations.
- `app/Enums` : statuts et types metier.
- `resources/views` : vues Blade.
- `resources/views/livewire` : composants et pages Livewire.
- `resources/js/app.js` : montage des graphes ApexCharts.
- `database/migrations` : structure de la base.
- `database/seeders` : roles, permissions et donnees demo.
- `tests/Feature` : tests fonctionnels des workflows.

## 4. Routes principales

Les routes sont declarees dans `routes/web.php`.

Routes publiques :
- `/` redirige vers `/dashboard` si l'utilisateur est connecte, sinon vers `/login`.
- Les routes d'authentification viennent de `routes/auth.php`.

Routes authentifiees :
- `/dashboard` : dashboard de pilotage.
- `/products` : catalogue produits.
- `/products/{product}` : fiche produit.
- `/products/{product}/movements` : entree, sortie ou ajustement.
- `/categories` : categories.
- `/suppliers` : fournisseurs.
- `/inventories` : inventaires.
- `/alerts` : alertes.
- `/forecasts` : previsions.
- `/reports` : rapports.
- `/users` : utilisateurs.
- `/roles` : roles.
- `/profile` : profil.

Les permissions sont appliquees route par route avec Spatie Permission.

## 5. Modeles et donnees

### User

Table : `users`.

Un utilisateur represente une personne pouvant se connecter a StockFlow.

Champs principaux :
- `name`
- `email`
- `password`
- `status` : `active` ou `disabled`
- `last_login_at`

Relations :
- un utilisateur a plusieurs mouvements de stock ;
- un utilisateur a plusieurs inventaires ;
- un utilisateur a des roles via Spatie Permission.

### Category

Table : `categories`.

Une categorie regroupe des produits.

Champs :
- `name`
- `description`
- `status` : `active` ou `archived`

Relation :
- une categorie a plusieurs produits.

### Supplier

Table : `suppliers`.

Un fournisseur est associe aux produits.

Champs :
- `name`
- `contact_name`
- `phone`
- `email`
- `address`
- `status` : `active` ou `archived`

Relation :
- un fournisseur a plusieurs produits.

### Product

Table : `products`.

Un produit est l'element central du stock.

Champs :
- `category_id`
- `supplier_id`
- `name`
- `sku`
- `barcode`
- `purchase_price`
- `sale_price`
- `current_stock`
- `critical_stock`
- `unit`
- `status` : `active` ou `archived`

Relations :
- appartient a une categorie ;
- appartient a un fournisseur ;
- a plusieurs mouvements de stock ;
- a plusieurs alertes ;
- a plusieurs previsions ;
- a une derniere prevision (`latestForecast`) ;
- a plusieurs lignes d'inventaire.

### StockMovement

Table : `stock_movements`.

Un mouvement represente une operation immutable sur le stock.

Types :
- `ENTRY` : entree.
- `EXIT` : sortie.
- `ADJUSTMENT` : ajustement.

Champs :
- `product_id`
- `user_id`
- `type`
- `quantity`
- `stock_before`
- `stock_after`
- `reference`
- `notes`
- `movement_date`

Relations :
- appartient a un produit ;
- appartient a un utilisateur.

### Inventory

Table : `inventories`.

Un inventaire represente un comptage physique.

Statuts :
- `draft` : brouillon.
- `validated` : valide.

Champs :
- `user_id`
- `inventory_date`
- `status`
- `notes`
- `validated_at`

Relations :
- appartient a un utilisateur ;
- a plusieurs lignes d'inventaire.

### InventoryItem

Table : `inventory_items`.

Une ligne d'inventaire represente un produit compte pendant un inventaire.

Champs :
- `inventory_id`
- `product_id`
- `expected_quantity` : quantite attendue au moment de la creation.
- `actual_quantity` : quantite observee.
- `difference` : ecart calcule.

Contraintes :
- unicite sur `inventory_id + product_id`.

### Alert

Table : `alerts`.

Une alerte indique qu'un produit est au seuil critique ou sous ce seuil.

Statuts :
- `new` : nouvelle.
- `viewed` : consultee.
- `resolved` : resolue.

Champs :
- `product_id`
- `type` : actuellement `critical_stock`
- `message`
- `status`
- `triggered_at`
- `resolved_at`

Relation :
- appartient a un produit.

### Forecast

Table : `forecasts`.

Une prevision estime le risque de rupture d'un produit.

Champs :
- `product_id`
- `average_daily_usage` : consommation moyenne journaliere.
- `predicted_out_date` : date estimee de rupture.
- `recommended_quantity` : quantite recommandee.
- `risk_score` : score numerique sur 100.
- `generated_at`

Relation :
- appartient a un produit.

Labels de risque :
- `100` : Critique.
- `75` : Eleve.
- `50` : Modere.
- `25` : Faible.

## 6. Etat actuel de la base locale

Base locale actuelle : `database/database.sqlite`.

Volumes releves :

| Table | Lignes |
|---|---:|
| users | 1 |
| categories | 1 |
| suppliers | 1 |
| products | 12 |
| stock_movements | 83 |
| inventories | 6 |
| inventory_items | 27 |
| alerts | 10 |
| forecasts | 24 |
| roles | 3 |
| permissions | 28 |

Repartitions actuelles :

| Donnee | Repartition |
|---|---|
| Produits | 12 actifs |
| Utilisateurs | 1 actif |
| Alertes | 5 nouvelles, 5 resolues |
| Inventaires | 1 brouillon, 5 valides |
| Mouvements | 36 entrees, 33 sorties, 14 ajustements |

Important : les seeders recreent une base de demonstration, mais la base locale actuelle contient aussi des donnees ajoutees pendant les tests manuels de presentation, notamment des inventaires de demo valides.

## 7. Seeders

### DatabaseSeeder

Fichier : `database/seeders/DatabaseSeeder.php`.

Il lance :
- `RolePermissionSeeder`
- creation ou mise a jour du compte admin :
  - email : `admin@stockflow.local`
  - mot de passe : `password`
  - role : `Administrateur`
- `DemoDataSeeder`

### RolePermissionSeeder

Fichier : `database/seeders/RolePermissionSeeder.php`.

Il cree les 28 permissions et les 3 roles :
- Administrateur.
- Gestionnaire.
- Magasinier.

### DemoDataSeeder

Fichier : `database/seeders/DemoDataSeeder.php`.

Il cree :
- 1 categorie : Produits pharmaceutiques.
- 1 fournisseur : Centrale Sante Benin.
- 12 produits de demo.
- Des mouvements d'entree et de sortie varies dans le temps.
- Les alertes generees automatiquement par les mouvements.
- Les previsions via `ForecastService::generateAll()`.

Avant de recreer les mouvements d'un produit de demo, le seeder supprime ses previsions, alertes et mouvements, puis remet son stock courant a 0.

## 8. Services metier

Les services sont dans `app/Services`.

### StockService

Fichier : `app/Services/StockService.php`.

Responsabilites :
- enregistrer les entrees de stock ;
- enregistrer les sorties de stock ;
- ajuster le stock vers une quantite cible ;
- verrouiller le produit pendant l'operation ;
- interdire les mouvements sur produits archives ;
- interdire le stock negatif ;
- creer un mouvement immutable ;
- mettre a jour `products.current_stock` ;
- declencher l'evaluation des alertes.

Methodes principales :
- `enter(Product, User, quantity, reference, notes)`
- `exit(Product, User, quantity, reference, notes)`
- `adjustTo(Product, User, targetStock, reference, notes)`

### AlertService

Fichier : `app/Services/AlertService.php`.

Responsabilites :
- verifier si un produit est au seuil critique ;
- creer une alerte si aucune alerte ouverte n'existe ;
- eviter les doublons d'alertes ouvertes ;
- resoudre automatiquement une alerte si le stock repasse au-dessus du seuil ;
- marquer une alerte comme consultee ;
- resoudre manuellement une alerte.

Regle principale :
- si `current_stock <= critical_stock`, le produit doit avoir au maximum une alerte ouverte ;
- si `current_stock > critical_stock`, une alerte ouverte existante passe a `resolved` et renseigne `resolved_at`.

### ForecastService

Fichier : `app/Services/ForecastService.php`.

Responsabilites :
- calculer les previsions produit par produit ;
- calculer toutes les previsions des produits actifs ;
- enregistrer chaque prevision dans `forecasts`.

Constantes :
- historique : 90 jours ;
- objectif de couverture : 60 jours.

### InventoryService

Fichier : `app/Services/InventoryService.php`.

Responsabilites :
- valider un inventaire ;
- verrouiller l'inventaire ;
- recalculer les differences ;
- generer les mouvements `ADJUSTMENT` ;
- passer l'inventaire en `validated` ;
- renseigner `validated_at`.

Une fois valide, un inventaire n'est plus modifiable.

### DashboardService

Fichier : `app/Services/DashboardService.php`.

Responsabilites :
- fournir les indicateurs principaux du dashboard ;
- charger les alertes ouvertes recentes ;
- charger les previsions les plus risquees ;
- charger les mouvements recents ;
- fournir les datasets des graphes du dashboard.

Graphes dashboard :
- activite entrees / sorties sur 7 jours ;
- repartition des risques ;
- top produits exposes.

### VisualizationService

Fichier : `app/Services/VisualizationService.php`.

Responsabilites :
- fournir les datasets de visualisation hors dashboard ;
- alimenter le graphe des alertes en attente ;
- alimenter les graphes de previsions ;
- alimenter le graphe d'evolution de stock produit ;
- alimenter le graphe des ecarts d'inventaire.

Visualisations actuelles :
- `/alerts` : lignes verticales par produit en alerte en attente.
- `/forecasts` : jours restants, quantites recommandees, produits les plus consommes.
- `/products/{id}` : evolution du stock sur 90 jours.
- `/inventories` : ecarts positifs / negatifs d'inventaire.

### ReportService

Fichier : `app/Services/ReportService.php`.

Responsabilites :
- construire les donnees exportables ;
- fournir les rapports disponibles ;
- produire les lignes et colonnes des exports.

Rapports disponibles :
- etat du stock ;
- produits critiques ;
- historique des mouvements ;
- resultats des inventaires ;
- previsions de rupture.

Les exports sont geres par `ReportController` :
- PDF via DomPDF ;
- Excel via Maatwebsite Excel.

## 9. Formules de calcul

### Consommation moyenne journaliere

La consommation moyenne journaliere est calculee sur les sorties des 90 derniers jours.

Formule :

```text
CMJ = Total des sorties sur 90 jours / 90
```

Exemple :

```text
Total sorties = 450
CMJ = 450 / 90 = 5
```

### Jours restants

Formule :

```text
Jours restants = Stock actuel / CMJ
```

Si `CMJ = 0`, le systeme considere que la rupture n'est pas estimable.

### Date de rupture estimee

Formule :

```text
Date rupture = Date du calcul + ceil(Jours restants)
```

Si `CMJ = 0` :
- `predicted_out_date = NULL`
- affichage : Non estimable.

### Score de risque

Le score reste stocke sous forme numerique, mais l'interface affiche un mot.

Regles :

| Condition | Score | Libelle |
|---|---:|---|
| jours restants <= 7 | 100 | Critique |
| jours restants <= 15 | 75 | Eleve |
| jours restants <= 30 | 50 | Modere |
| sinon | 25 | Faible |

Cas particulier :
- si `CMJ = 0`, score = 25, libelle = Faible.

### Quantite recommandee

Objectif : couvrir 60 jours de consommation.

Formule :

```text
Quantite recommandee = (CMJ x 60) - Stock actuel
```

Si le resultat est negatif :

```text
Quantite recommandee = 0
```

Exemple :

```text
Stock actuel = 120
Sorties 90 jours = 450
CMJ = 450 / 90 = 5
Jours restants = 120 / 5 = 24
Risque = Modere
Quantite recommandee = (5 x 60) - 120 = 180
```

### Graphe des alertes en attente

Le graphe en haut de `/alerts` ne montre que les alertes ouvertes :
- `new`
- `viewed`

Il ignore les alertes resolues.

Pour chaque produit :

```text
Ratio = Stock actuel / Stock critique
```

Le ratio est borne pour l'affichage :

```text
Ratio affiche = min(max(Ratio, 0), 1.5)
```

Hauteur de la barre :

```text
Hauteur = (Ratio affiche / 1.5) x 100
```

La ligne horizontale de seuil correspond au ratio `1`, donc au stock critique.

Les couleurs des barres indiquent le niveau de stock par rapport au seuil :
- rouge fonce : rupture ;
- rose : tres critique ;
- orange : sous seuil ;
- vert : repasse au-dessus du seuil.

Les tags visibles sur ce graphe sont les tags de risque issus de la derniere prevision du produit :
- Critique ;
- Eleve ;
- Modere ;
- Faible.

Le graphe n'affiche pas le statut d'alerte (`Nouvelle`, `Consultee`, `Resolue`) pour ne pas melanger etat d'alerte et risque produit.

## 10. Workflows principaux

### WF-01 : creation d'un produit

Acteur :
- Gestionnaire ou Administrateur avec `products.create`.

Etapes :
1. Ouvrir le module Produits.
2. Cliquer sur Ajouter un produit.
3. Choisir une categorie active.
4. Choisir un fournisseur actif.
5. Renseigner nom, SKU, code-barres, prix achat, prix vente, unite, stock critique.
6. Soumettre.
7. Le systeme valide les donnees.
8. Le produit est cree avec `current_stock = 0`.

Resultat :
- produit disponible dans le catalogue.

### WF-02 : entree de stock

Acteur :
- Gestionnaire, Administrateur ou Magasinier avec `stock.entry`.

Etapes :
1. Ouvrir une fiche produit active.
2. Saisir une entree de stock.
3. Renseigner quantite, reference, note.
4. Valider.

Traitement :
1. `StockMovementController` verifie le type et la permission.
2. `StockService::enter()` verrouille le produit.
3. Le systeme calcule `stock_after = stock_before + quantity`.
4. Un mouvement `ENTRY` est cree.
5. `products.current_stock` est mis a jour.
6. `AlertService::evaluate()` verifie les alertes.

Resultat :
- stock augmente ;
- une alerte ouverte peut etre resolue automatiquement si le stock repasse au-dessus du seuil.

### WF-03 : sortie de stock

Acteur :
- Gestionnaire, Administrateur ou Magasinier avec `stock.exit`.

Etapes :
1. Ouvrir une fiche produit active.
2. Saisir une sortie de stock.
3. Renseigner quantite, reference, note.
4. Valider.

Traitement :
1. `StockService::exit()` verrouille le produit.
2. Le systeme calcule `stock_after = stock_before - quantity`.
3. Si `stock_after < 0`, l'operation est refusee.
4. Sinon, un mouvement `EXIT` est cree.
5. `products.current_stock` est mis a jour.
6. `AlertService::evaluate()` verifie les alertes.

Resultat :
- stock diminue ;
- une alerte peut etre creee si le stock atteint ou descend sous le seuil critique.

### WF-04 : ajustement direct

Acteur :
- Gestionnaire ou Administrateur avec `stock.adjustment`.

Etapes :
1. Ouvrir une fiche produit active.
2. Saisir le stock reel cible.
3. Valider.

Traitement :
1. `StockService::adjustTo()` verifie que le stock cible n'est pas negatif.
2. Le produit est verrouille.
3. Si le stock cible est identique au stock courant, aucun mouvement n'est cree.
4. Sinon, un mouvement `ADJUSTMENT` est cree.
5. `products.current_stock` devient la valeur cible.
6. Les alertes sont reevaluees.

Resultat :
- stock synchronise manuellement.

### WF-05 : inventaire

Acteur :
- Gestionnaire ou Administrateur.

Etapes :
1. Creer un inventaire.
2. Selectionner les produits actifs.
3. Le systeme cree les lignes avec :
   - `expected_quantity = current_stock`
   - `actual_quantity = current_stock`
   - `difference = 0`
4. Saisir les quantites observees.
5. Enregistrer le comptage.
6. Valider l'inventaire.

Traitement de validation :
1. `InventoryService::validate()` verrouille l'inventaire.
2. Pour chaque ligne :
   - `difference = actual_quantity - expected_quantity`
3. Si la difference est non nulle :
   - `StockService::adjustTo()` cree un mouvement `ADJUSTMENT`.
4. L'inventaire passe a `validated`.
5. `validated_at` est renseigne.

Resultat :
- le stock est synchronise avec la realite physique ;
- les mouvements d'ajustement sont historises ;
- les alertes sont creees ou resolues selon le nouveau stock.

### WF-06 : alertes

Declencheurs :
- entree de stock ;
- sortie de stock ;
- ajustement ;
- validation d'inventaire.

Traitement :
1. Le systeme lit le stock courant du produit.
2. Si `current_stock <= critical_stock` :
   - recherche d'une alerte ouverte (`new` ou `viewed`) ;
   - si aucune alerte ouverte n'existe, creation d'une alerte `new`.
3. Si `current_stock > critical_stock` :
   - une alerte ouverte existante passe a `resolved` ;
   - `resolved_at` est renseigne.

Consultation :
- ouvrir une alerte `new` la passe en `viewed`.

Resolution manuelle :
- possible avec `alerts.resolve`.

Resultat :
- une seule alerte active par produit ;
- les alertes resolues restent conservees pour l'historique.

### WF-07 : previsions

Declencheurs :
- recalcul manuel depuis `/forecasts` ;
- `ForecastService::generateAll()` ;
- logique prevue pour job planifie.

Traitement :
1. Lecture des sorties des 90 derniers jours.
2. Calcul de la CMJ.
3. Calcul des jours restants.
4. Estimation de la date de rupture.
5. Calcul du score de risque.
6. Calcul de la quantite recommandee.
7. Enregistrement dans `forecasts`.

Resultat :
- une nouvelle prevision est creee ;
- l'interface utilise la derniere prevision par produit.

### WF-08 : dashboard

Acteur :
- utilisateur avec `forecasts.view`.

Traitement :
- si l'utilisateur n'a pas `forecasts.view`, il est redirige vers Produits ou Profil ;
- le dashboard affiche les indicateurs, alertes ouvertes, previsions prioritaires et mouvements recents.

Graphes :
- activite entrees / sorties sur 7 jours ;
- repartition des risques ;
- produits les plus exposes.

### WF-09 : rapports

Acteur :
- utilisateur avec `reports.view` ;
- export avec `reports.export`.

Etapes :
1. Ouvrir Rapports.
2. Choisir un rapport.
3. Exporter en PDF ou Excel.

Rapports :
- etat du stock ;
- produits critiques ;
- mouvements ;
- inventaires ;
- previsions.

### WF-10 : utilisateurs et roles

Acteur :
- Administrateur.

Utilisateurs :
- voir ;
- creer ;
- modifier ;
- desactiver / reactiver.

Roles :
- voir ;
- creer ;
- modifier ;
- attribuer des permissions.

Regle :
- au moins un administrateur actif doit rester disponible.

## 11. Permissions

Permissions existantes :

```text
users.view
users.create
users.update
users.disable
roles.view
roles.manage
products.view
products.create
products.update
products.archive
categories.view
categories.create
categories.update
suppliers.view
suppliers.create
suppliers.update
stock.view
stock.entry
stock.exit
stock.adjustment
inventories.view
inventories.create
inventories.validate
alerts.view
alerts.resolve
reports.view
reports.export
forecasts.view
```

Roles :

| Role | Permissions |
|---|---|
| Administrateur | toutes |
| Gestionnaire | toutes sauf `users.*` et `roles.manage` |
| Magasinier | `products.view`, `stock.view`, `stock.entry`, `stock.exit` |

## 12. Validations et regles metier

### Produit

- `name` : obligatoire, max 255 caracteres.
- `sku` : obligatoire, unique.
- `barcode` : optionnel, unique si renseigne.
- `purchase_price` : obligatoire, >= 0.
- `sale_price` : obligatoire, >= prix d'achat.
- `critical_stock` : obligatoire, >= 0.
- `category_id` : categorie active obligatoire.
- `supplier_id` : fournisseur actif obligatoire.

### Fournisseur

- `name` : obligatoire.
- `phone` : obligatoire.
- `email` : optionnel, format email valide.

### Mouvement

- `quantity` : obligatoire.
- pour entree / sortie : quantite > 0.
- pour ajustement : stock cible >= 0.
- `type` : `ENTRY`, `EXIT`, `ADJUSTMENT`.

### Inventaire

- `inventory_date` : obligatoire.
- `product_ids` : au moins un produit actif.
- `actual_quantity` : >= 0.
- inventaire valide : non modifiable.

## 13. Cas limites

- Stock negatif interdit.
- SKU duplique interdit.
- Code-barres duplique interdit si renseigne.
- Produit archive non modifiable.
- Produit archive non selectionnable pour mouvement.
- Fournisseur archive non selectionnable pour nouveau produit.
- Categorie archive non selectionnable pour nouveau produit.
- Suppression physique evitee pour les entites metier : on archive ou desactive.
- Mouvement cree : immutable dans l'interface.
- Inventaire valide : non modifiable.
- Alerte resolue : conservee dans l'historique.
- Alerte resolue : peut etre recreee si la condition critique reapparait.

## 14. Pages et visualisations

### Dashboard

Vue : `resources/views/dashboard.blade.php`.

Contenu :
- cartes indicateurs ;
- activite entrees / sorties ;
- repartition des risques ;
- produits les plus exposes ;
- tableau previsions ;
- alertes ouvertes ;
- mouvements recents.

### Alertes

Vue : `resources/views/alerts/index.blade.php`.

Contenu :
- graphe vertical des produits en alerte en attente ;
- tableau Nouvelles alertes ;
- tableau Anciennes alertes consultees ;
- tableau Alertes resolues.

Le graphe ne montre pas les alertes resolues.

### Previsions

Vue : `resources/views/forecasts/index.blade.php`.

Contenu :
- jours restants avant rupture ;
- quantites recommandees ;
- produits les plus consommes ;
- tableau des previsions.

Precision UX :
- un produit peut etre critique mais absent du graphe des plus grosses quantites recommandees si sa quantite a commander est faible.
- exemple observe : l'insuline est critique mais sa recommandation est faible en volume.

### Produit

Vue : `resources/views/products/show.blade.php`.

Contenu :
- cartes stock, seuil, categorie, fournisseur, risque ;
- formulaires entree / sortie / ajustement selon permissions ;
- graphe d'evolution du stock sur 90 jours ;
- historique des mouvements.

### Inventaires

Vue : `resources/views/inventories/index.blade.php`.

Contenu :
- graphe des ecarts d'inventaire valides ;
- liste des inventaires.

## 15. Frontend et graphes

Le fichier `resources/js/app.js` monte les graphes ApexCharts.

Principe :
- chaque conteneur de graphe possede `data-chart-kind` ;
- les donnees sont injectees en JSON via `data-chart` ;
- au chargement ou apres navigation Livewire, le script detruit les anciens graphes et remonte ceux de la page courante.

Types actuels :
- `risk-distribution`
- `movement-activity`
- `top-risks`
- `forecast-days`
- `forecast-recommended`
- `top-consumption`
- `product-stock`
- `inventory-differences`

Le graphe des alertes n'utilise pas ApexCharts. Il est fait en HTML / Tailwind pour controler finement les lignes verticales, les tags et la ligne de seuil.

## 16. Tests

Les tests principaux sont dans `tests/Feature`.

Ils couvrent notamment :
- authentification ;
- permissions de navigation ;
- workflow produit / mouvements ;
- workflow alertes / previsions ;
- workflow inventaire ;
- rapports ;
- utilisateurs et roles ;
- matrice de permissions ;
- donnees demo dashboard.

Commandes utiles :

```bash
php artisan test
npm.cmd run build
```

Etat recent :
- suite complete passee avec 52 tests et 281 assertions.
- build Vite OK, avec un avertissement connu sur la taille du chunk ApexCharts.

## 17. Resume architecture metier

Flux central :

```text
Produit
  -> Mouvements de stock
  -> Mise a jour du stock courant
  -> Evaluation des alertes
  -> Calcul des previsions
  -> Dashboard / Alertes / Rapports
```

Flux inventaire :

```text
Inventaire brouillon
  -> Lignes d'inventaire
  -> Saisie quantites observees
  -> Validation
  -> Mouvements ADJUSTMENT
  -> Mise a jour stock
  -> Evaluation alertes
```

Flux prevision :

```text
Sorties historiques
  -> CMJ
  -> Jours restants
  -> Date de rupture
  -> Score de risque
  -> Quantite recommandee
```

StockFlow est donc actuellement structure autour de trois moteurs :
- le moteur de stock : `StockService` ;
- le moteur d'alertes : `AlertService` ;
- le moteur de previsions : `ForecastService`.

Les autres services organisent les usages :
- `InventoryService` pour les inventaires ;
- `DashboardService` pour la synthese ;
- `VisualizationService` pour les graphes ;
- `ReportService` pour les exports.
