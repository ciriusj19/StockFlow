# Mémoire de soutenance - StockFlow

## Page de garde

Sujet : Conception et réalisation d'une application web de gestion intelligente des stocks avec alertes, prévisions de rupture et synthèse décisionnelle.

Projet : StockFlow

Auteur : À compléter

Filière : À compléter

Établissement : À compléter

Encadreur : À compléter

Année académique : À compléter

## Informations à compléter avant la version finale

Pour produire une version définitive conforme aux attentes de l'établissement, les informations suivantes doivent être précisées :

- nom complet de l'auteur ;
- nom de l'établissement ;
- filière ou spécialité ;
- nom de l'encadreur ;
- année académique exacte ;
- exigences de mise en page imposées par l'école, si elles existent ;
- intitulé officiel à conserver pour le sujet.

[PAGE_BREAK]

## Résumé

La gestion des stocks constitue un enjeu important pour les organisations qui manipulent des produits physiques. Une rupture de stock peut ralentir l'activité, provoquer des pertes financières, désorganiser les services internes et dégrader la qualité de service. À l'inverse, un surstock peut immobiliser inutilement des ressources et rendre la gestion opérationnelle moins efficace. Dans ce contexte, il devient nécessaire de disposer d'un outil capable non seulement d'enregistrer les mouvements de stock, mais aussi d'alerter les responsables et de produire des indicateurs utiles à la décision.

Ce mémoire présente la conception et la réalisation de StockFlow, une application web mono-entreprise de gestion de stock orientée aide à la décision. L'application permet de gérer un catalogue de produits, les catégories, les fournisseurs, les entrées et sorties de stock, les inventaires physiques, les alertes de seuil critique, les prévisions de rupture, les rapports exportables et une synthèse décisionnelle. La solution repose sur une chaîne de traitement claire : les mouvements opérationnels alimentent les alertes, les alertes et mouvements alimentent les prévisions, puis les prévisions et indicateurs consolidés alimentent une synthèse destinée aux responsables.

Le projet met en évidence une démarche proche de la Business Intelligence. Les données opérationnelles sont conservées dans des tables métier, tandis qu'une couche analytique intégrée, composée de tables `analytics_*`, consolide les indicateurs nécessaires à la décision. Cette approche permet de proposer un mini Data Mart dans la même base de données, adapté au périmètre d'un MVP, tout en ouvrant la possibilité d'une évolution future vers un véritable Data Warehouse.

Mots-clés : gestion de stock, alerte, prévision de rupture, Business Intelligence, Data Mart, tableau de bord, aide à la décision, Laravel.

## Abstract

Inventory management is a major issue for organizations that handle physical products. Stockouts can disrupt operations, generate financial losses and reduce service quality, while overstocks can immobilize resources and reduce operational efficiency. In this context, organizations need tools that go beyond recording stock movements. They also need alerts, forecasts and decision-oriented indicators.

This dissertation presents the design and implementation of StockFlow, a single-company web application for inventory management and decision support. The application manages products, categories, suppliers, stock entries and exits, physical inventories, critical stock alerts, stockout forecasts, exportable reports and a decision summary. The solution follows a clear decision chain: operational movements feed alerts, alerts and movements feed forecasts, and consolidated indicators feed a decision-oriented dashboard.

The project demonstrates a Business Intelligence approach. Operational data is stored in business tables, while an integrated analytical layer based on `analytics_*` tables consolidates decision indicators. This approach provides a lightweight Data Mart inside the same database, suitable for an MVP, while preparing the system for future evolution toward a complete Data Warehouse.

Keywords: inventory management, alert, stockout forecasting, Business Intelligence, Data Mart, dashboard, decision support, Laravel.

[PAGE_BREAK]

## Table des matières indicative

1. Introduction générale
2. Chapitre 1 : Cadre théorique et conceptuel
3. Chapitre 2 : Analyse des besoins et conception
4. Chapitre 3 : Réalisation de l'application StockFlow
5. Chapitre 4 : Tests, résultats et discussion
6. Conclusion générale
7. Bibliographie indicative
8. Annexes

Note : la table des matières finale devra être générée automatiquement dans Word après validation de la structure.

[PAGE_BREAK]

## Introduction générale

### Contexte

La gestion de stock occupe une place centrale dans de nombreuses organisations. Les produits doivent être disponibles au bon moment, en quantité suffisante, sans pour autant générer une immobilisation excessive de ressources. Cette exigence concerne aussi bien les entreprises commerciales que les structures médicales, les dépôts, les agences, les magasins internes ou les organisations disposant d'un stock physique à suivre.

Dans plusieurs contextes, le suivi du stock repose encore sur des fichiers manuels, des feuilles de calcul ou des procédures dispersées. Ces méthodes peuvent suffire au départ, mais elles deviennent vite limitées lorsque le nombre de produits augmente, lorsque plusieurs acteurs interviennent ou lorsque les responsables ont besoin d'une vision synthétique pour prendre des décisions. Le problème ne se limite donc pas à l'enregistrement des produits : il concerne aussi la capacité du système à détecter les risques, à anticiper les ruptures et à fournir des informations consolidées.

StockFlow s'inscrit dans ce cadre. L'application vise à relier la gestion quotidienne du stock à une démarche d'aide à la décision. Elle permet de passer d'une logique purement opérationnelle, centrée sur les entrées et sorties, à une logique plus analytique, fondée sur les alertes, les prévisions et la synthèse décisionnelle.

### Problématique

Une gestion de stock efficace nécessite des données fiables, une traçabilité des opérations et une lecture claire des risques. Pourtant, les utilisateurs opérationnels et les décideurs n'ont pas les mêmes besoins. Le magasinier doit enregistrer rapidement les mouvements. Le responsable stock doit surveiller les seuils, les inventaires et les prévisions. Le chef d'agence ou le directeur général doit plutôt disposer d'indicateurs condensés pour les réunions et les décisions stratégiques.

La problématique retenue est donc la suivante :

```text
Comment concevoir une application de gestion de stock capable de transformer les mouvements opérationnels en indicateurs décisionnels exploitables pour anticiper les ruptures et améliorer la prise de décision ?
```

Cette problématique met en relation trois dimensions :

- la dimension opérationnelle, liée aux mouvements et aux inventaires ;
- la dimension tactique, liée aux alertes et aux prévisions ;
- la dimension stratégique, liée à la synthèse décisionnelle et aux rapports.

### Objectif général

L'objectif général du projet est de concevoir et réaliser une application web permettant de gérer les stocks, de détecter automatiquement les situations critiques, d'anticiper les ruptures et de fournir une synthèse décisionnelle exploitable par les responsables.

### Objectifs spécifiques

Les objectifs spécifiques sont les suivants :

- gérer un catalogue de produits, catégories et fournisseurs ;
- enregistrer les entrées, sorties et ajustements de stock ;
- interdire les mouvements qui produisent un stock négatif ;
- déclencher automatiquement une alerte lorsque le stock atteint le seuil critique ;
- résoudre automatiquement une alerte lorsque le stock repasse au-dessus du seuil ;
- calculer des prévisions de rupture à partir des mouvements de sortie ;
- recommander une quantité de réapprovisionnement ;
- distinguer les niveaux de décision par rôles et permissions ;
- produire une synthèse décisionnelle compilée manuellement ;
- exporter les rapports et synthèses en PDF ou Excel.

### Hypothèses et périmètre

Le projet est volontairement conçu comme un MVP mono-entreprise. Il ne couvre pas encore le multi-tenant, la facturation, les abonnements ou la gestion de plusieurs sociétés. Ce choix permet de concentrer l'effort sur la cohérence métier, la traçabilité du stock, les alertes, les prévisions et la synthèse décisionnelle.

Le projet ne prétend pas non plus mettre en œuvre un modèle d'intelligence artificielle. Les prévisions reposent sur une méthode simple et explicable : la consommation moyenne journalière calculée sur les mouvements de sortie des 90 derniers jours.

### Méthodologie

La réalisation de StockFlow suit une démarche progressive :

- analyse des workflows métier ;
- définition des règles de gestion ;
- modélisation des données ;
- mise en place des rôles et permissions ;
- développement des modules opérationnels ;
- ajout des alertes et prévisions ;
- création d'une couche de synthèse décisionnelle ;
- réalisation des tests fonctionnels ;
- amélioration de l'interface et des visualisations.

### Annonce du plan

Le mémoire est structuré en quatre chapitres. Le premier chapitre présente le cadre théorique et les notions nécessaires à la compréhension du projet. Le deuxième chapitre détaille l'analyse des besoins et la conception. Le troisième chapitre décrit la réalisation de l'application. Le quatrième chapitre présente les tests, les résultats obtenus, les limites et les perspectives.

[PAGE_BREAK]

## Chapitre 1 : Cadre théorique et conceptuel

### Système d'information

Un système d'information est un ensemble organisé de ressources permettant de collecter, traiter, stocker et diffuser des informations utiles au fonctionnement d'une organisation. Il ne se limite pas à un logiciel : il inclut aussi les utilisateurs, les procédures, les règles métier, les données et les outils techniques.

Dans le cadre de StockFlow, le système d'information permet de collecter les mouvements de stock, de traiter les règles de seuil critique, de conserver l'historique des opérations et de restituer des indicateurs sous forme de tableaux de bord, de prévisions et de rapports.

La valeur du système vient de sa capacité à structurer l'information. Une entrée de stock n'est pas seulement une ligne enregistrée en base ; elle peut résoudre une alerte, modifier une prévision et influencer une synthèse décisionnelle. C'est cette circulation de l'information qui constitue le cœur du projet.

### Gestion de stock

La gestion de stock consiste à suivre les quantités disponibles, les mouvements entrants et sortants, les seuils de sécurité et les besoins de réapprovisionnement. Elle vise à maintenir un équilibre entre disponibilité et maîtrise des ressources.

Les principales notions utilisées dans StockFlow sont :

- le stock actuel ;
- le stock critique ;
- l'entrée de stock ;
- la sortie de stock ;
- l'ajustement ;
- l'inventaire physique ;
- la rupture ;
- le réapprovisionnement.

Un stock critique correspond au niveau à partir duquel une attention particulière est nécessaire. Lorsque le stock actuel devient inférieur ou égal à ce seuil, StockFlow crée une alerte. Cette alerte ne corrige pas le stock elle-même ; elle signale une situation à traiter. La correction se fait par une entrée de stock, un ajustement ou un inventaire.

### Inventaire physique

L'inventaire physique permet de comparer le stock théorique enregistré dans l'application avec le stock réellement observé. Il joue un rôle important dans la fiabilité des données, car les erreurs de saisie, pertes, oublis ou écarts de manipulation peuvent créer une différence entre la base et la réalité.

Dans StockFlow, un inventaire est créé en brouillon, puis validé après saisie des quantités observées. La validation génère des mouvements de type `ADJUSTMENT`, afin de conserver une trace de la correction appliquée. Cette logique préserve l'historique et évite les modifications silencieuses du stock.

### Alertes de stock

Une alerte est un signal généré lorsqu'un produit atteint une situation critique. Dans StockFlow, une alerte de stock est créée lorsque le stock actuel est inférieur ou égal au stock critique.

La règle de création est la suivante :

```text
Si stock actuel <= stock critique
Alors créer une alerte ouverte si aucune alerte ouverte n'existe déjà pour ce produit.
```

La règle de résolution est la suivante :

```text
Si le stock repasse au-dessus du seuil critique
Alors l'alerte ouverte passe à l'état résolu et la date de résolution est enregistrée.
```

L'application conserve l'historique des alertes. Une alerte résolue n'est donc pas supprimée, car elle peut servir à analyser les situations passées.

### Prévision de rupture

La prévision de rupture consiste à estimer le moment où le stock d'un produit pourrait devenir insuffisant. Dans StockFlow, cette prévision repose sur la consommation moyenne journalière. Le système utilise les mouvements de sortie des 90 derniers jours pour calculer une moyenne, puis estime le nombre de jours restants.

La formule de base est :

```text
CMJ = Total des sorties sur 90 jours / 90
```

Lorsque la consommation moyenne journalière est nulle, la rupture n'est pas estimable. Le système affiche alors une information claire pour l'utilisateur, au lieu de produire une date artificielle.

Cette approche n'est pas un modèle statistique avancé. Elle est volontairement simple, transparente et défendable pour un MVP. Elle permet d'expliquer facilement les résultats au jury et aux utilisateurs.

### Business Intelligence

La Business Intelligence désigne l'ensemble des méthodes et outils permettant de transformer des données en informations utiles à la décision. Elle s'appuie sur la collecte, le traitement, l'analyse et la restitution des données.

Dans StockFlow, la Business Intelligence apparaît à travers :

- les indicateurs de stock ;
- les alertes ;
- les prévisions ;
- les tableaux de bord ;
- les rapports exportables ;
- la synthèse décisionnelle ;
- les tables analytiques `analytics_*`.

L'application montre donc comment une donnée opérationnelle peut être transformée en indicateur décisionnel.

### Data Warehouse et Data Mart

Un Data Warehouse est une base orientée analyse, conçue pour centraliser, historiser et structurer les données utiles à la décision. Un Data Mart est une version plus restreinte, souvent centrée sur un domaine fonctionnel particulier.

StockFlow ne met pas encore en place un Data Warehouse séparé. Pour le MVP, l'application intègre une couche analytique dans la même base de données. Cette couche est composée de tables `analytics_*` qui consolident les données opérationnelles.

Cette approche peut être présentée comme un mini Data Mart intégré. Elle permet de séparer les données opérationnelles des indicateurs de décision, sans complexifier excessivement l'architecture.

### SIAD

Un système interactif d'aide à la décision, ou SIAD, aide les responsables à analyser une situation et à choisir une action. StockFlow peut être présenté comme un SIAD appliqué à la gestion de stock, car il ne se limite pas à enregistrer des opérations. Il signale les risques, calcule des prévisions, priorise les produits et propose une synthèse exploitable en réunion.

### KPI

Un KPI est un indicateur clé de performance. Dans StockFlow, les KPI permettent de comprendre rapidement l'état du stock et les priorités d'action.

Les principaux KPI sont :

- nombre de produits actifs ;
- nombre de produits sous seuil critique ;
- nombre d'alertes ouvertes ;
- nombre de risques élevés ;
- quantité totale recommandée ;
- score de qualité des données ;
- risque moyen par catégorie ;
- dépendance fournisseur ;
- fiabilité inventaire.

### Contrôle d'accès par rôles

Le contrôle d'accès par rôles, ou RBAC, consiste à attribuer des permissions à des rôles plutôt qu'à chaque utilisateur individuellement. StockFlow utilise cette logique pour représenter les niveaux de décision.

Les rôles principaux sont :

- Administrateur ;
- Magasinier ;
- Responsable stock ;
- Chef d'agence ;
- Directeur général.

Cette séparation évite qu'un utilisateur opérationnel accède à des fonctions stratégiques ou administratives qui ne correspondent pas à son niveau de responsabilité.

[PAGE_BREAK]

## Chapitre 2 : Analyse des besoins et conception

### Présentation du besoin

Le besoin principal est de disposer d'une application capable de gérer le stock au quotidien tout en produisant des informations utiles à la décision. L'utilisateur opérationnel doit pouvoir enregistrer les mouvements rapidement. Le responsable stock doit pouvoir suivre les alertes, vérifier les inventaires et anticiper les ruptures. Le décideur doit pouvoir consulter une synthèse claire sans entrer dans le détail de chaque alerte.

L'application doit donc répondre à trois niveaux :

- niveau opérationnel : saisie et suivi quotidien ;
- niveau tactique : surveillance, alerte et anticipation ;
- niveau stratégique : synthèse et décision.

### Acteurs

Les acteurs retenus sont :

- Magasinier : enregistre les entrées et sorties de stock ;
- Responsable stock : gère les produits, alertes, prévisions et inventaires ;
- Chef d'agence : compile et consulte la synthèse décisionnelle ;
- Directeur général : consulte les rapports et les synthèses stratégiques ;
- Administrateur : gère les utilisateurs, rôles et permissions.

### Besoins fonctionnels

Les besoins fonctionnels sont :

- authentifier les utilisateurs ;
- gérer les rôles et permissions ;
- gérer les produits ;
- gérer les catégories ;
- gérer les fournisseurs ;
- enregistrer les entrées de stock ;
- enregistrer les sorties de stock ;
- ajuster le stock ;
- créer et valider les inventaires ;
- générer les alertes de stock critique ;
- résoudre automatiquement les alertes lorsque le stock est corrigé ;
- calculer les prévisions de rupture ;
- afficher les tableaux de bord ;
- compiler une synthèse décisionnelle ;
- exporter les rapports en PDF ou Excel.

### Besoins non fonctionnels

Les besoins non fonctionnels sont :

- interface lisible et moderne ;
- séparation claire des rôles ;
- traçabilité des opérations ;
- interdiction du stock négatif ;
- conservation de l'historique ;
- simplicité de lancement en local ;
- structure évolutive ;
- tests automatisés des workflows principaux.

### Diagramme de cas d'utilisation

[Diagramme à insérer dans la version finale]

Description textuelle :

```text
Administrateur -> gérer utilisateurs, rôles et permissions
Magasinier -> consulter produits, enregistrer entrées et sorties
Responsable stock -> gérer produits, alertes, prévisions et inventaires
Chef d'agence -> compiler et exporter la synthèse décisionnelle
Directeur général -> consulter les rapports et exports stratégiques
```

### Workflow général

Le workflow principal est le suivant :

```text
Produit
-> Entrée de stock
-> Sortie de stock
-> Alerte
-> Prévision
-> Dashboard
-> Synthèse décisionnelle
```

Cette chaîne montre le passage d'une action opérationnelle à une information de décision.

### Workflow de création d'un produit

Le responsable stock ouvre le module Produits, clique sur l'ajout d'un produit, sélectionne une catégorie et un fournisseur, puis renseigne le nom, le SKU, les prix, l'unité et le stock critique. Le système valide les données et crée le produit avec un stock initial à zéro.

Les règles principales sont :

- le nom est obligatoire ;
- le SKU est obligatoire et unique ;
- le prix d'achat est positif ou nul ;
- le prix de vente doit être supérieur ou égal au prix d'achat ;
- le stock critique est positif ou nul ;
- un fournisseur archivé ne peut pas être sélectionné.

### Workflow d'entrée de stock

L'utilisateur autorisé ouvre la fiche produit et saisit une quantité reçue. Le système crée un mouvement de type `ENTRY`, calcule le nouveau stock et vérifie les alertes.

Si le stock repasse au-dessus du seuil critique, l'alerte ouverte est résolue automatiquement. La date de résolution est enregistrée.

### Workflow de sortie de stock

L'utilisateur autorisé saisit une quantité à sortir. Le système vérifie que le stock disponible est suffisant. Si la quantité demandée dépasse le stock actuel, l'opération est refusée. Sinon, le système crée un mouvement de type `EXIT`, met à jour le stock et vérifie les alertes.

### Workflow d'inventaire

Le responsable stock crée un inventaire, sélectionne les produits à compter, saisit les quantités observées puis valide l'inventaire. La validation calcule les écarts et génère des mouvements `ADJUSTMENT`. Le stock est ainsi synchronisé avec la réalité physique.

Depuis une alerte ouverte, l'utilisateur dispose également d'un bouton pour lancer un inventaire avec le produit concerné déjà sélectionné. Ce choix clarifie le fait qu'une alerte ne modifie pas le stock directement : elle oriente vers une action métier.

### Workflow des alertes

Une alerte est générée à la suite d'une entrée, d'une sortie ou d'un inventaire lorsque le stock atteint le seuil critique. L'application conserve une seule alerte active par produit afin d'éviter les doublons.

Sur la page de détail d'une alerte, l'utilisateur est orienté vers deux actions :

- entrer du stock ;
- faire un inventaire.

Ces deux actions correspondent aux corrections réelles du stock.

### Workflow des prévisions

Le système lit les sorties des 90 derniers jours, calcule la consommation moyenne journalière, estime le nombre de jours restants, détermine le niveau de risque et calcule une quantité recommandée.

Les prévisions permettent de passer d'une simple alerte de seuil à une anticipation de rupture.

### Workflow de synthèse décisionnelle

Le chef d'agence clique sur le bouton de compilation. Le service de compilation lit les produits, mouvements, alertes, prévisions et inventaires, puis crée un `analytics_run`. Les tables analytiques sont remplies pour produire des KPI consolidés par produit, catégorie, fournisseur et inventaire.

La synthèse peut ensuite être exportée pour une réunion.

### Modèle de données

Les tables opérationnelles sont :

- `users` ;
- `roles` ;
- `permissions` ;
- `categories` ;
- `suppliers` ;
- `products` ;
- `stock_movements` ;
- `inventories` ;
- `inventory_items` ;
- `alerts` ;
- `forecasts`.

Les tables analytiques sont :

- `analytics_runs` ;
- `analytics_product_kpis` ;
- `analytics_category_kpis` ;
- `analytics_supplier_kpis` ;
- `analytics_inventory_kpis`.

### Architecture applicative

L'application suit une architecture Laravel organisée autour des contrôleurs, services, modèles et vues.

```text
Utilisateur
-> Interface Blade / Livewire
-> Contrôleurs Laravel
-> Services métier
-> Modèles Eloquent
-> Base de données SQLite
```

Les services métier sont au centre de l'architecture :

- `StockService` ;
- `AlertService` ;
- `ForecastService` ;
- `InventoryService` ;
- `DashboardService` ;
- `AnalyticsCompilationService` ;
- `VisualizationService` ;
- `ReportService`.

Cette séparation évite de placer la logique métier dans les vues et facilite les tests.

[PAGE_BREAK]

## Chapitre 3 : Réalisation de l'application StockFlow

### Environnement technique

StockFlow est développé avec les technologies suivantes :

- PHP 8.2 ou supérieur ;
- Laravel 12 ;
- Laravel Breeze ;
- Livewire 3 et Volt ;
- Blade ;
- Tailwind CSS ;
- Alpine.js ;
- ApexCharts ;
- Spatie Laravel Permission ;
- DomPDF ;
- Maatwebsite Excel ;
- SQLite en développement local.

Le choix de Laravel permet de bénéficier d'une structure MVC claire, d'un système de migration robuste, d'une validation fiable et d'une bonne séparation entre les routes, contrôleurs, modèles et services.

### Structure du projet

Les dossiers importants sont :

- `app/Http/Controllers` : contrôleurs ;
- `app/Http/Requests` : validations de formulaires ;
- `app/Models` : modèles Eloquent ;
- `app/Services` : logique métier ;
- `app/Enums` : statuts et types métier ;
- `resources/views` : interfaces Blade ;
- `resources/js/app.js` : montage des graphes ;
- `database/migrations` : structure de la base ;
- `database/seeders` : rôles, permissions et données de démonstration ;
- `tests/Feature` : tests fonctionnels.

### Module produits

Le module Produits permet de gérer le catalogue. Chaque produit est relié à une catégorie et un fournisseur. Les champs importants sont le nom, le SKU, le code-barres, les prix, l'unité, le stock actuel, le stock critique et le statut.

Le produit constitue le centre du système, car les mouvements, alertes, prévisions et lignes d'inventaire lui sont rattachés.

### Module mouvements de stock

Les mouvements de stock sont de trois types :

- `ENTRY` : entrée ;
- `EXIT` : sortie ;
- `ADJUSTMENT` : ajustement.

Chaque mouvement conserve le stock avant et le stock après. Cette règle garantit la traçabilité.

Le stock négatif est interdit. Une sortie supérieure au stock disponible est refusée.

### Module inventaires

Le module Inventaires permet de créer un comptage physique. Lorsqu'un inventaire est validé, les écarts sont transformés en mouvements d'ajustement.

Cette approche présente deux avantages :

- le stock est corrigé ;
- l'historique de correction reste visible.

### Module alertes

Le module Alertes surveille les produits au seuil critique. Les alertes sont séparées en trois groupes :

- nouvelles alertes ;
- alertes consultées ;
- alertes résolues.

Une alerte ouverte peut être résolue automatiquement si le stock repasse au-dessus du seuil. L'interface oriente également l'utilisateur vers deux actions concrètes : entrer du stock ou faire un inventaire.

### Module prévisions

Le module Prévisions calcule le risque de rupture. Il s'appuie sur les sorties des 90 derniers jours.

Formules utilisées :

```text
CMJ = Total sorties / 90
Jours restants = Stock actuel / CMJ
Date rupture = Aujourd'hui + Jours restants
Quantité recommandée = (CMJ * 60) - Stock actuel
```

Si la consommation moyenne est nulle, le système indique que la rupture n'est pas estimable.

### Module dashboard opérationnel

Le dashboard opérationnel présente l'état actuel du stock. Il affiche notamment :

- les produits actifs ;
- les stocks critiques ;
- les ruptures ;
- les alertes ouvertes ;
- les risques élevés ;
- l'activité sur 30 jours ;
- les produits les plus exposés.

Il met aussi en évidence la chaîne de décision :

```text
Mouvements -> Alertes -> Prévisions -> Synthèse
```

### Module synthèse décisionnelle

La synthèse décisionnelle est destinée aux responsables qui n'ont pas besoin de traiter chaque alerte séparément. Elle présente une vision consolidée de l'état du stock.

Elle contient :

- la dernière compilation ;
- la période analysée ;
- l'auteur ;
- les sources utilisées ;
- les KPI principaux ;
- la qualité des données ;
- les produits prioritaires ;
- le risque par catégorie ;
- la dépendance fournisseur ;
- la fiabilité inventaire ;
- l'historique des compilations.

La compilation est manuelle dans le MVP. Ce choix est justifié par le besoin de préparer une synthèse au moment opportun, par exemple avant une réunion.

### Module rapports

Les rapports permettent d'exporter des données en PDF ou Excel. Cette fonctionnalité répond au besoin de produire des supports pour les réunions et la prise de décision.

### Interface utilisateur

L'interface est organisée autour d'une sidebar sombre gris-bleu. Les couleurs logiques sont conservées :

- vert pour les entrées, succès et éléments résolus ;
- rouge pour les sorties et situations critiques ;
- orange ou ambre pour les risques ;
- bleu pour la marque, les actions principales et les informations structurantes.

Les graphes utilisent des couleurs pleines afin d'améliorer la lisibilité et d'éviter les dégradés trop clairs.

[PAGE_BREAK]

## Chapitre 4 : Tests, résultats et discussion

### Stratégie de test

Les tests automatisés couvrent les workflows principaux. Ils vérifient les produits, mouvements, alertes, prévisions, inventaires, rapports, rôles, permissions et accès utilisateurs.

Les tests fonctionnels se trouvent dans `tests/Feature`.

### Tests des alertes et prévisions

Les tests vérifient que :

- une alerte est créée lorsque le stock atteint le seuil critique ;
- la consultation d'une alerte la marque comme consultée ;
- la résolution conserve l'historique ;
- les alertes sont séparées en nouvelles, consultées et résolues ;
- le responsable stock peut recalculer les prévisions ;
- le magasinier ne peut pas ouvrir les alertes ou prévisions.

### Tests des inventaires

Les tests vérifient que :

- le responsable stock peut créer un inventaire ;
- les quantités attendues sont reprises au moment de la création ;
- la validation génère un ajustement ;
- un inventaire validé ne peut plus être modifié ;
- le magasinier ne peut pas créer un inventaire ;
- un produit peut être préselectionné dans l'inventaire depuis une alerte.

### Tests des rôles et permissions

Les tests vérifient que :

- l'administrateur voit les modules d'administration ;
- le magasinier ne voit que les fonctions opérationnelles ;
- le responsable stock accède aux alertes, prévisions et inventaires ;
- le chef d'agence accède à la synthèse décisionnelle ;
- le directeur général accède aux rapports.

### Résultats obtenus

Les derniers tests automatisés exécutés indiquent :

```text
51 tests passés
308 assertions
```

Ces résultats confirment que les workflows principaux sont couverts et que les règles métier critiques sont vérifiées.

### Discussion

StockFlow atteint l'objectif principal du MVP : transformer les mouvements opérationnels en informations utiles à la décision. Les entrées, sorties et inventaires alimentent les alertes. Les mouvements de sortie alimentent les prévisions. Les prévisions, alertes et inventaires alimentent la synthèse décisionnelle.

Le projet montre donc une progression cohérente :

```text
Donnée opérationnelle
-> Règle métier
-> Indicateur
-> Visualisation
-> Décision
```

Cette progression est intéressante pour la soutenance, car elle montre que l'application ne se limite pas à un CRUD. Elle met en œuvre une logique d'aide à la décision.

### Limites

Les principales limites sont :

- l'application est mono-entreprise ;
- il n'existe pas encore de vrai Data Warehouse séparé ;
- la prévision repose sur une moyenne simple ;
- il n'y a pas encore de saisonnalité ;
- il n'y a pas de modèle de machine learning ;
- la compilation décisionnelle est manuelle ;
- les données de démonstration restent limitées par rapport à un environnement réel.

Ces limites correspondent au périmètre d'un MVP. Elles ne remettent pas en cause la cohérence du projet, mais elles ouvrent des perspectives d'amélioration.

### Perspectives

Les évolutions possibles sont :

- passage vers une architecture SaaS ;
- gestion de plusieurs entreprises ;
- séparation physique de la base analytique ;
- mise en place d'un processus ETL ou ELT ;
- historisation plus avancée ;
- intégration d'une saisonnalité ;
- détection d'anomalies ;
- notifications email ;
- bons de commande ;
- multi-entrepôts ;
- application mobile pour le magasinier.

[PAGE_BREAK]

## Conclusion générale

StockFlow répond à un besoin concret : améliorer la gestion de stock en reliant les opérations quotidiennes à une logique d'alerte, de prévision et de synthèse décisionnelle. L'application permet de gérer les produits, les mouvements, les inventaires, les alertes et les prévisions, tout en distinguant les responsabilités selon les rôles utilisateurs.

Le projet met en évidence l'intérêt d'un système d'information structuré. Une entrée ou une sortie de stock ne reste pas une simple opération isolée ; elle influence les alertes, les prévisions et les indicateurs de pilotage. Cette transformation de la donnée en information constitue l'apport principal de StockFlow.

La couche analytique intégrée permet de présenter une démarche proche de la Business Intelligence, sans imposer la complexité d'un Data Warehouse complet. Elle constitue une base évolutive pour un futur système décisionnel plus avancé.

Le MVP est volontairement limité à une application mono-entreprise. Ce choix permet de défendre une solution claire, stable et explicable. Les perspectives d'évolution vers un SaaS, un Data Warehouse séparé ou des modèles prédictifs plus avancés restent ouvertes.

En conclusion, StockFlow ne se limite pas à enregistrer des stocks. Il transforme les opérations quotidiennes en indicateurs utiles à la décision.

[PAGE_BREAK]

## Bibliographie indicative

Cette bibliographie devra être complétée et normalisée selon les exigences de l'établissement.

- Ouvrages et cours sur les systèmes d'information de gestion.
- Ouvrages et cours sur la gestion de stock.
- Références sur la Business Intelligence.
- Références sur les Data Warehouse et Data Mart.
- Documentation officielle Laravel.
- Documentation officielle Livewire.
- Documentation officielle Spatie Laravel Permission.
- Documentation officielle ApexCharts.

[PAGE_BREAK]

## Annexes proposées

### Annexe A : Formules de prévision

```text
CMJ = Total des sorties sur 90 jours / 90
Jours restants = Stock actuel / CMJ
Date rupture = Aujourd'hui + Jours restants
Quantité recommandée = (CMJ * 60) - Stock actuel
```

### Annexe B : Matrice des rôles

```text
Administrateur : toutes les permissions
Magasinier : produits, stock entry, stock exit
Responsable stock : produits, stock, alertes, prévisions, inventaires
Chef d'agence : synthèse décisionnelle
Directeur général : synthèse décisionnelle et rapports
```

### Annexe C : Diagrammes à produire

- diagramme de cas d'utilisation ;
- diagramme de classes ;
- diagramme de base de données ;
- diagramme de séquence pour l'entrée de stock ;
- diagramme de séquence pour l'alerte ;
- diagramme de séquence pour la prévision ;
- diagramme de flux opérationnel vers décisionnel ;
- schéma de la couche analytique.
