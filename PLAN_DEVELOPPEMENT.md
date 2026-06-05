# Plan de developpement - StockFlow

## 1. Vision du MVP

StockFlow est une application web mono-entreprise de gestion et de prevision des stocks pour PME.

L'objectif principal du MVP est de demontrer un workflow complet et lisible :

```text
Produit -> Entree de stock -> Sortie de stock -> Alerte -> Prevision -> Dashboard
```

La priorite de presentation est donc :

1. Alertes de stock critique.
2. Previsions de rupture.
3. Dashboard de synthese.
4. Socle de gestion stock necessaire pour rendre les alertes et previsions credibles.

Le MVP ne couvre pas :

- Multi-tenant.
- Gestion de plusieurs societes.
- Abonnements.
- Facturation.
- Super-admin SaaS.
- Suppression physique des donnees historiques.

## 2. Stack technique

- Backend : Laravel 12.
- Frontend : Livewire 3, Volt, Tailwind CSS.
- Base de donnees : MySQL.
- Authentification : Laravel Breeze.
- Permissions : Spatie Laravel Permission.
- Rapports : Laravel Excel, DomPDF.
- Graphiques : ApexCharts.
- Versioning : Git et GitHub.

## 3. Architecture cible

Structure applicative recommandee :

```text
app/
  Models/
  Services/
    StockService
    InventoryService
    AlertService
    ForecastService
    DashboardService
  Actions/
  DTOs/
  Enums/
  Policies/
  Jobs/
    GenerateForecastsJob
    GenerateAlertsJob
  Http/
    Requests/
    Middleware/
resources/
  views/
  livewire/
routes/
  web.php
database/
  migrations/
  seeders/
  factories/
```

Regle d'architecture principale :

- Les changements de stock passent toujours par `StockService`.
- Les alertes sont gerees par `AlertService`.
- Les previsions sont gerees par `ForecastService`.
- Les composants Livewire appellent des actions ou services, pas de logique metier lourde dans les vues.

## 4. Modele de donnees MVP

### Tables principales

- `users` : utilisateurs applicatifs.
- `roles` et tables Spatie : roles et permissions.
- `categories` : familles de produits.
- `suppliers` : fournisseurs.
- `products` : catalogue produits.
- `stock_movements` : historique immutable des entrees, sorties et ajustements.
- `inventories` : campagnes d'inventaire.
- `inventory_items` : lignes d'inventaire.
- `alerts` : alertes de stock.
- `forecasts` : resultats de prevision.

### Champs metier importants

`products`

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
- `status`

`stock_movements`

- `product_id`
- `user_id`
- `type` : `ENTRY`, `EXIT`, `ADJUSTMENT`
- `quantity`
- `stock_before`
- `stock_after`
- `reference`
- `notes`
- `movement_date`

`alerts`

- `product_id`
- `type`
- `message`
- `status` : `new`, `viewed`, `resolved`
- `triggered_at`
- `resolved_at`

`forecasts`

- `product_id`
- `average_daily_usage`
- `predicted_out_date`
- `recommended_quantity`
- `risk_score`
- `generated_at`

## 5. Regles metier non negociables

### Stock

- Le stock negatif est interdit.
- Le stock n'est jamais modifie directement depuis les formulaires.
- Toute variation de stock cree un mouvement.
- `products.current_stock` est un cache metier pour l'affichage rapide.
- `current_stock` est mis a jour uniquement apres creation d'un mouvement valide.
- Chaque mouvement conserve `stock_before` et `stock_after`.
- Un mouvement valide devient non modifiable.
- Un produit archive n'est plus modifiable.
- Un produit archive n'est pas selectionnable pour un mouvement.

### Produits

- Chaque produit appartient a une seule categorie.
- Chaque produit possede un fournisseur principal.
- Chaque produit possede un SKU unique.
- Chaque produit possede une unite de mesure.
- Un produit n'est jamais supprime physiquement, il est archive.
- Le stock initial d'un produit cree est toujours `0`.

### Fournisseurs et categories

- Un fournisseur archive n'est pas selectionnable pour un nouveau produit.
- Les categories et fournisseurs sont archives, pas supprimes physiquement.

### Inventaires

- Un inventaire compare le stock theorique au stock reel.
- Les ecarts sont historises dans les lignes d'inventaire.
- Quand un ecart est valide, un mouvement `ADJUSTMENT` est genere.
- Un inventaire valide devient non modifiable.

### Alertes

- Une alerte critique est creee si `current_stock <= critical_stock`.
- Une seule alerte ouverte peut exister par produit.
- Apres entree, ajustement ou inventaire, si le stock repasse au-dessus du seuil critique, l'alerte ouverte passe automatiquement a `resolved`.
- `resolved_at` est renseigne lors de la resolution automatique.
- Une alerte resolue reste conservee pour l'historique.
- Une alerte resolue peut etre recreee si la condition critique reapparait.
- L'utilisateur peut consulter manuellement les alertes.

### Previsions

- Les previsions ne modifient jamais le stock reel.
- Les previsions sont recalculables.
- Le calcul s'appuie sur les mouvements de sortie des 90 derniers jours.
- Le job Laravel de prevision s'execute chaque nuit a `00:00`.

Formules :

```text
CMJ = Total sorties des 90 derniers jours / 90
Jours restants = Stock actuel / CMJ
Date de rupture = Aujourd'hui + Jours restants
Quantite recommandee = (CMJ x 60) - Stock actuel
```

Niveau de risque :

- Si jours restants <= 7 : risque `100`.
- Si jours restants <= 15 : risque `75`.
- Si jours restants <= 30 : risque `50`.
- Sinon : risque `25`.

Cas `CMJ = 0` :

- `predicted_out_date = NULL`.
- `risk_score = 25`.
- `recommended_quantity = 0`.
- Interface : afficher `Non estimable` ou `Aucune consommation observee`.

## 6. Roles et permissions

### Roles

`Administrateur`

- Toutes les permissions.
- Ne peut pas supprimer definitivement les donnees historiques.

`Gestionnaire`

- Gestion operationnelle des stocks.
- Toutes les permissions sauf `users.*` et `roles.manage`.

`Magasinier`

- Consultation produits.
- Entrees de stock.
- Sorties de stock.
- Consultation stock.
- Consultation de son historique.

### Permissions

USERS :

- `users.view`
- `users.create`
- `users.update`
- `users.disable`

ROLES :

- `roles.view`
- `roles.manage`

PRODUCTS :

- `products.view`
- `products.create`
- `products.update`
- `products.archive`

CATEGORIES :

- `categories.view`
- `categories.create`
- `categories.update`

SUPPLIERS :

- `suppliers.view`
- `suppliers.create`
- `suppliers.update`

STOCK :

- `stock.view`
- `stock.entry`
- `stock.exit`
- `stock.adjustment`

INVENTORIES :

- `inventories.view`
- `inventories.create`
- `inventories.validate`

ALERTS :

- `alerts.view`
- `alerts.resolve`

REPORTS :

- `reports.view`
- `reports.export`

FORECASTS :

- `forecasts.view`

## 7. Plan de livraison

### Phase 0 - Initialisation du projet

Objectif : mettre en place une base Laravel propre.

Travaux :

- Creer le projet Laravel 12.
- Configurer Git.
- Configurer MySQL.
- Installer Breeze avec Livewire.
- Installer Livewire 3, Volt et Tailwind CSS.
- Installer Spatie Laravel Permission.
- Installer Laravel Excel, DomPDF et ApexCharts.
- Configurer les variables `.env`.
- Mettre en place le layout applicatif de base.

Livrables :

- Application Laravel demarrable localement.
- Authentification fonctionnelle.
- Page dashboard vide accessible apres connexion.

Critere d'acceptation :

- Un utilisateur peut se connecter et acceder a l'espace applicatif.

### Phase 1 - Socle securite et permissions

Objectif : controler les acces avant de construire les modules metier.

Travaux :

- Creer les roles `Administrateur`, `Gestionnaire`, `Magasinier`.
- Creer toutes les permissions MVP.
- Assigner les permissions par role.
- Ajouter les seeders de roles, permissions et utilisateur administrateur.
- Ajouter les policies ou middleware necessaires.
- Masquer les entrees de navigation selon les permissions.

Livrables :

- Systeme de roles operationnel.
- Navigation adaptee au role connecte.
- Seed de donnees initiales.

Critere d'acceptation :

- Un magasinier ne voit pas les modules utilisateurs, roles, statistiques globales ou rapports non autorises.

### Phase 2 - Referentiels produits

Objectif : permettre la creation du catalogue necessaire aux mouvements.

Travaux :

- Creer migrations, modeles, factories et validations pour categories.
- Creer migrations, modeles, factories et validations pour fournisseurs.
- Creer migrations, modeles, factories et validations pour produits.
- Implementer les ecrans Livewire/Volt :
  - Liste categories.
  - Creation et modification categories.
  - Liste fournisseurs.
  - Creation et modification fournisseurs.
  - Liste produits.
  - Creation produit.
  - Modification produit.
  - Fiche produit.
  - Archivage produit.
- Ajouter recherche multicritere produit.
- Empecher la selection de fournisseurs archives.
- Empecher la modification de produits archives.

Validations produit :

- `name` obligatoire, maximum 255 caracteres.
- `sku` obligatoire et unique.
- `purchase_price` obligatoire, >= 0.
- `sale_price` obligatoire, >= prix d'achat.
- `critical_stock` obligatoire, >= 0.

Validations fournisseur :

- `name` obligatoire.
- `phone` obligatoire.
- `email` facultatif, format email valide.

Livrables :

- Catalogue produits complet.
- Categories et fournisseurs exploitables.
- Produits crees avec `current_stock = 0`.

Critere d'acceptation :

- Un gestionnaire peut creer un produit complet et le retrouver dans le catalogue.

### Phase 3 - Mouvements de stock

Objectif : rendre le stock tracable et fiable.

Travaux :

- Creer table `stock_movements`.
- Creer enum de type mouvement : `ENTRY`, `EXIT`, `ADJUSTMENT`.
- Implementer `StockService`.
- Implementer entree de stock.
- Implementer sortie de stock.
- Implementer ajustement de stock.
- Enregistrer `stock_before`, `stock_after`, `user_id`, `movement_date`.
- Interdire les sorties superieures au stock disponible.
- Interdire la selection de produits archives.
- Rendre les mouvements non modifiables apres creation.
- Ajouter historique complet des mouvements.

Validation mouvement :

- `quantity` obligatoire et > 0.
- `type` obligatoire parmi `ENTRY`, `EXIT`, `ADJUSTMENT`.
- `reference` facultative mais affichee dans l'historique.
- `notes` facultatives.

Livrables :

- Entrees de stock.
- Sorties de stock.
- Ajustements.
- Historique des mouvements.

Critere d'acceptation :

- Une sortie qui depasse le stock disponible est refusee sans creer de mouvement.

### Phase 4 - Alertes critiques

Objectif : faire apparaitre la valeur principale de la presentation.

Travaux :

- Creer table `alerts`.
- Creer enum statut alerte : `new`, `viewed`, `resolved`.
- Implementer `AlertService`.
- Declencher la verification d'alerte apres chaque mouvement.
- Creer une alerte si `current_stock <= critical_stock` et aucune alerte ouverte n'existe.
- Ne pas dupliquer les alertes ouvertes.
- Resoudre automatiquement l'alerte ouverte si `current_stock > critical_stock`.
- Renseigner `resolved_at` a la resolution.
- Ajouter page liste des alertes.
- Ajouter consultation manuelle d'une alerte.
- Ajouter action manuelle de resolution si l'utilisateur a `alerts.resolve`.

Livrables :

- Alertes automatiques.
- Une seule alerte active par produit.
- Historique des alertes resolues.

Critere d'acceptation :

- Une sortie qui fait passer un produit sous son seuil critique cree une alerte visible dans le dashboard.

### Phase 5 - Inventaires

Objectif : synchroniser le stock theorique avec le stock reel.

Travaux :

- Creer tables `inventories` et `inventory_items`.
- Implementer `InventoryService`.
- Creer un inventaire.
- Selectionner les produits.
- Saisir les quantites observees.
- Calculer les ecarts.
- Valider l'inventaire.
- Generer les mouvements `ADJUSTMENT` pour les ecarts valides.
- Declencher la verification des alertes apres ajustement.
- Rendre un inventaire valide non modifiable.

Validation inventaire :

- `actual_quantity` >= 0.

Livrables :

- Creation et validation d'inventaire.
- Generation automatique des ajustements.
- Historique des ecarts.

Critere d'acceptation :

- Valider un inventaire avec ecart met a jour le stock via un mouvement `ADJUSTMENT`.

### Phase 6 - Previsions de rupture

Objectif : produire les indicateurs de risque pour la presentation.

Travaux :

- Creer table `forecasts`.
- Implementer `ForecastService`.
- Implementer `GenerateForecastsJob`.
- Planifier le job chaque nuit a `00:00`.
- Lire les sorties des 90 derniers jours.
- Calculer CMJ.
- Calculer jours restants.
- Calculer date de rupture.
- Calculer risque.
- Calculer quantite recommandee.
- Gerer le cas `CMJ = 0`.
- Ajouter page liste des previsions.
- Ajouter fiche prevision par produit.

Livrables :

- Previsions enregistrees.
- Risque de rupture visible.
- Quantite recommandee visible.

Critere d'acceptation :

- Pour un produit avec 450 sorties sur 90 jours et 120 en stock, le systeme affiche CMJ `5`, rupture dans `24` jours, risque `50`, quantite recommandee `180`.

### Phase 7 - Dashboard de presentation

Objectif : presenter clairement l'etat du stock, les alertes et les previsions.

Travaux :

- Implementer `DashboardService`.
- Ajouter cartes de synthese :
  - Nombre de produits.
  - Produits critiques.
  - Produits en rupture.
  - Alertes ouvertes.
  - Previsions de rupture.
  - Mouvements recents.
- Ajouter graphiques ApexCharts :
  - Repartition des risques.
  - Evolution des mouvements.
  - Top produits a risque.
- Ajouter liens directs vers alertes et previsions.

Livrables :

- Dashboard utile pour la demo.
- Synthese immediate des risques.

Critere d'acceptation :

- Le dashboard raconte le workflow : le produit sorti apparait comme critique, avec alerte et prevision associees.

### Phase 8 - Rapports et exports

Objectif : fournir les exports attendus dans le document.

Travaux :

- Export PDF et Excel de l'etat du stock.
- Export PDF et Excel de l'historique des mouvements.
- Export PDF et Excel des produits critiques.
- Export PDF et Excel des resultats d'inventaire.
- Export PDF et Excel des previsions.
- Controler les permissions `reports.view` et `reports.export`.

Livrables :

- Module rapports.
- Exports utilisables pour la presentation.

Critere d'acceptation :

- Un gestionnaire peut exporter la liste des produits critiques en PDF et Excel.

### Phase 9 - Donnees de demo et finition

Objectif : rendre la presentation fluide et convaincante.

Travaux :

- Creer seeders de demonstration :
  - Categories.
  - Fournisseurs.
  - Produits.
  - Mouvements historiques.
  - Alertes.
  - Previsions.
- Creer un scenario demo reproductible.
- Ajouter messages de succes et d'erreur clairs.
- Harmoniser l'interface Tailwind.
- Verifier les vues mobile et desktop.
- Nettoyer les libelles metier.

Livrables :

- Jeu de donnees de demo.
- Parcours de presentation stable.

Critere d'acceptation :

- La demo peut etre executee du debut a la fin sans manipulation technique.

## 8. Scenario de demonstration recommande

1. Connexion comme gestionnaire.
2. Creation d'une categorie.
3. Creation d'un fournisseur.
4. Creation d'un produit avec stock critique.
5. Entree de stock pour initialiser le stock disponible.
6. Sortie de stock qui rapproche le produit du seuil critique.
7. Sortie supplementaire qui declenche l'alerte.
8. Consultation de l'alerte ouverte.
9. Affichage de la prevision de rupture.
10. Affichage du dashboard avec produit critique, alerte et risque.
11. Entree de stock qui repasse au-dessus du seuil critique.
12. Verification de la resolution automatique de l'alerte.

## 9. Tests a prevoir

### Tests unitaires

- Calcul du stock apres entree.
- Calcul du stock apres sortie.
- Refus du stock negatif.
- Calcul des ajustements.
- Creation d'alerte critique.
- Non-duplication des alertes ouvertes.
- Resolution automatique d'alerte.
- Calcul CMJ.
- Calcul du risque.
- Calcul de la quantite recommandee.
- Cas `CMJ = 0`.

### Tests fonctionnels

- Creation produit.
- Archivage produit.
- Produit archive non modifiable.
- Produit archive non selectionnable pour mouvement.
- Creation entree.
- Creation sortie.
- Sortie superieure au stock refusee.
- Validation inventaire.
- Generation mouvement `ADJUSTMENT`.
- Acces refuse selon permissions.

### Tests de presentation

- Workflow complet produit -> entree -> sortie -> alerte -> prevision -> dashboard.
- Donnees de demo coherentes.
- Exports principaux generes.
- Dashboard visible et comprehensible.

## 10. Ordre de priorite si le temps est limite

Priorite 1 :

- Authentification.
- Roles et permissions.
- Produits, categories, fournisseurs.
- Entrees et sorties de stock.
- Alertes automatiques.
- Previsions.
- Dashboard.

Priorite 2 :

- Inventaires.
- Ajustements.
- Rapports PDF et Excel.

Priorite 3 :

- Graphiques avances.
- Filtres avances.
- Exports multiples.
- Finitions visuelles.

Pour une presentation courte, ne pas sacrifier alertes, previsions et dashboard. Ce sont les elements qui montrent le mieux la valeur de StockFlow.

