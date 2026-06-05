# StockFlow

StockFlow est une application Laravel mono-entreprise de gestion, alerte et prevision des stocks pour PME.

Le MVP suit le parcours de demonstration valide :

```text
Produit -> Entree -> Sortie -> Alerte -> Prevision -> Dashboard
```

## Fonctionnalites MVP

- Catalogue produits, categories et fournisseurs.
- Entrees, sorties et ajustements de stock via mouvements immuables.
- Inventaires physiques avec validation et mouvements `ADJUSTMENT`.
- Alertes critiques automatiques, consultation et resolution.
- Previsions de rupture sur les sorties des 90 derniers jours.
- Dashboard avec graphiques de risques, mouvements et produits exposes.
- Rapports PDF et Excel : stock, produits critiques, mouvements, inventaires, previsions.
- Administration interne des utilisateurs et roles.
- Application mono-entreprise : pas de multi-tenant, pas d'abonnement, pas de facturation.

## Compte de demo

```text
Email: admin@stockflow.local
Mot de passe: password
Role: Administrateur
```

Les inscriptions publiques sont desactivees. Les comptes sont crees depuis le module `Administration > Utilisateurs`.

Le seed de demonstration cree 15 produits, 15 previsions, plusieurs alertes critiques et des mouvements repartis sur les 7 derniers jours pour alimenter les graphiques du dashboard.

## Installation locale

### Demarrage rapide sous Windows

Le plus simple pour utiliser l'application :

```bat
ouvrir-stockflow.cmd
```

Ce raccourci ouvre une fenetre serveur, puis ouvre le navigateur sur :

```text
http://127.0.0.1:8000/login
```

Pour arreter l'application, ferme la fenetre serveur ou fais `Ctrl+C` dedans.

### Demarrage avec controles

Depuis `cmd`, ouvre le dossier du projet puis tape :

```bat
lancer-stockflow.cmd
```

Ce script verifie PHP, `.env`, la base SQLite, les migrations et les assets compiles, puis lance l'application sur :

```text
http://127.0.0.1:8000/login
```

Si le port `8000` est deja occupe :

```bat
set STOCKFLOW_PORT=8001
lancer-stockflow.cmd
```

Identifiants de demo :

```text
admin@stockflow.local
password
```

### Installation complete

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

Avec SQLite, verifier que le fichier suivant existe avant la migration :

```bash
database/database.sqlite
```

## Verification

```bash
php artisan test
npm run build
php artisan schedule:list
```

Etat valide lors de la derniere passe locale :

```text
55 tests reussis
388 assertions
```

## Jobs planifies

- `GenerateForecastsJob` : tous les jours a `00:00`.
- `GenerateAlertsJob` : toutes les heures.

## Scenario de presentation

1. Se connecter avec le compte administrateur.
2. Ouvrir le dashboard pour voir les alertes et les previsions.
3. Consulter les produits critiques : `Insuline rapide`, `Amoxicilline 500 mg`, `Gants nitrile` ou `Bandelettes glycemie`.
4. Ouvrir une alerte et verifier son statut.
5. Ouvrir une prevision et verifier CMJ, date de rupture, risque et quantite recommandee.
6. Ouvrir `Masques chirurgicaux` pour montrer le cas `Non estimable`.
7. Creer une entree de stock qui repasse au-dessus du seuil critique.
8. Verifier la resolution automatique de l'alerte.
9. Faire un ajustement manuel si besoin depuis la fiche produit.
10. Exporter un rapport PDF ou Excel depuis `Rapports`.

## Roles de base

- `Administrateur` : toutes les permissions.
- `Responsable stock` : operations metier, sans gestion utilisateurs ni gestion des roles.
- `Magasinier` : consultation produits et mouvements entree/sortie.
- `Chef d'agence` : synthese decisionnelle, compilation et exports analytics.
- `Directeur general` : synthese decisionnelle et rapports.

Un compte desactive ne peut pas se connecter. Une session deja ouverte est fermee automatiquement a la requete suivante.
