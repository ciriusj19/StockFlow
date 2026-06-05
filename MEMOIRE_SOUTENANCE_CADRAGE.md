# Cadrage du memoire et de la soutenance - StockFlow

Document de travail avant redaction finale.

Ce document sert de repere pour retrouver rapidement le cadre academique, les notions importantes, les choix de conception et les arguments a utiliser pour presenter StockFlow devant un jury. Il ne remplace pas le memoire final : il prepare sa structure et fixe ce que l'application doit demontrer.

## 1. Idee centrale du projet

StockFlow est une application web de gestion et d'aide a la decision pour le suivi des stocks.

L'idee forte n'est pas seulement de gerer des produits, mais de montrer comment une donnee operationnelle peut devenir une information decisionnelle.

La chaine principale a defendre est :

```text
Mouvements de stock
-> Alertes
-> Previsions de rupture
-> Synthese decisionnelle
-> Decision de gestion
```

Le projet montre donc le passage :

- de la donnee brute ;
- vers une donnee traitee ;
- vers un indicateur ;
- vers une decision.

## 2. Cadre general

Le projet s'inscrit dans le domaine des systemes d'information de gestion, avec une orientation vers la Business Intelligence et l'aide a la decision.

Le contexte retenu est celui d'une structure qui gere des produits physiques et qui doit eviter :

- les ruptures de stock ;
- les surstocks inutiles ;
- les decisions prises trop tard ;
- les pertes d'information entre les operations quotidiennes et les responsables.

Le probleme n'est donc pas seulement technique. Il est aussi organisationnel :

- le magasinier manipule les entrees et sorties ;
- le responsable stock surveille les alertes, les inventaires et les previsions ;
- le chef d'agence consulte une synthese condensee ;
- le directeur general consulte des rapports strategiques ;
- l'administrateur gere les acces et les droits.

## 3. Problematique possible

Problematique principale :

```text
Comment concevoir une application de gestion de stock capable de transformer les mouvements operationnels en indicateurs decisionnels exploitables pour anticiper les ruptures et ameliorer la prise de decision ?
```

Variantes possibles :

- Comment passer d'une gestion reactive du stock a une gestion predictive et decisionnelle ?
- Comment integrer une couche de synthese proche d'un Data Warehouse dans une application de gestion de stock MVP ?
- Comment structurer les donnees de stock pour produire des alertes, des previsions et des indicateurs de pilotage fiables ?

## 4. Objectifs du projet

Objectif principal :

Mettre en place une application web permettant de gerer les stocks, de detecter les situations critiques, d'anticiper les ruptures et de fournir une synthese decisionnelle aux responsables.

Objectifs specifiques :

- gerer un catalogue de produits, categories et fournisseurs ;
- enregistrer les entrees, sorties et ajustements de stock ;
- interdire le stock negatif ;
- declencher automatiquement des alertes lorsque le stock atteint le seuil critique ;
- resoudre automatiquement une alerte lorsque le stock repasse au-dessus du seuil ;
- calculer des previsions de rupture a partir des sorties des 90 derniers jours ;
- recommander une quantite de reapprovisionnement ;
- produire une synthese decisionnelle compilee manuellement ;
- separer les droits selon les niveaux de decision ;
- exporter les rapports et syntheses en PDF ou Excel.

## 5. Perimetre retenu pour le MVP

Le MVP est mono-entreprise.

Il n'integre pas encore :

- le multi-tenant ;
- la gestion de plusieurs societes ;
- les abonnements ;
- la facturation ;
- un super-admin SaaS ;
- une base decisionnelle separee physiquement ;
- une intelligence artificielle ou un modele de machine learning.

Ce choix est volontaire. L'objectif est de construire un systeme stable, explicable et soutenable, avant d'envisager une evolution SaaS ou Data Warehouse complet.

## 6. Notions importantes a maitriser

### Systeme d'information

Un systeme d'information permet de collecter, traiter, stocker et restituer des informations utiles au fonctionnement d'une organisation.

Dans StockFlow :

- les mouvements collectent la realite operationnelle ;
- les services metier traitent les regles ;
- la base conserve l'historique ;
- les tableaux de bord restituent les informations utiles.

### Gestion de stock

La gestion de stock vise a maintenir un niveau de stock suffisant pour satisfaire les besoins, tout en evitant les exces.

Notions associees :

- stock actuel ;
- stock critique ;
- entree de stock ;
- sortie de stock ;
- ajustement ;
- inventaire ;
- rupture ;
- reapprovisionnement.

### Alerte de stock

Une alerte signale qu'un produit a atteint ou depasse une situation critique.

Regle StockFlow :

```text
Si stock actuel <= stock critique
Alors creer une alerte ouverte, si aucune alerte ouverte n'existe deja pour ce produit.
```

Regle de resolution :

```text
Si le stock repasse au-dessus du seuil critique
Alors l'alerte ouverte passe automatiquement a resolved
et la date resolved_at est enregistree.
```

### Prevision de rupture

La prevision estime le risque de rupture a partir de l'historique de consommation.

Elle ne pretend pas faire de l'intelligence artificielle. Elle utilise une approche simple, explicable et adaptee au MVP.

### Business Intelligence

La Business Intelligence consiste a transformer des donnees en indicateurs utiles a la decision.

Dans StockFlow, la BI apparait a travers :

- les KPI de stock ;
- les graphes ;
- les previsions ;
- les exports ;
- la synthese decisionnelle ;
- les tables `analytics_*`.

### Data Warehouse et Data Mart

Un Data Warehouse est une base orientee analyse, historisee et structuree pour la decision.

StockFlow ne possede pas encore un vrai Data Warehouse separe. Pour le MVP, l'application integre une couche proche d'un Data Mart avec les tables :

- `analytics_runs` ;
- `analytics_product_kpis` ;
- `analytics_category_kpis` ;
- `analytics_supplier_kpis` ;
- `analytics_inventory_kpis`.

Cette couche consolide les donnees operationnelles pour produire une vision decisionnelle.

### SIAD

Un SIAD est un systeme interactif d'aide a la decision.

StockFlow peut etre presente comme un SIAD applique a la gestion de stock, car il :

- collecte des donnees ;
- calcule des indicateurs ;
- signale les risques ;
- aide a prioriser les produits ;
- fournit une synthese exportable pour les reunions.

### KPI

Un KPI est un indicateur cle de performance.

KPI StockFlow :

- nombre de produits actifs ;
- nombre de produits sous seuil critique ;
- nombre d'alertes ouvertes ;
- nombre de risques eleves ;
- quantite totale recommandee ;
- score de qualite des donnees ;
- risque moyen par categorie ;
- dependance fournisseur ;
- fiabilite inventaire.

### RBAC

RBAC signifie Role-Based Access Control.

Dans StockFlow, les utilisateurs n'ont pas les memes droits selon leur role :

- Administrateur ;
- Magasinier ;
- Responsable stock ;
- Chef d'agence ;
- Directeur general.

Cette separation renforce la securite, mais aussi la logique decisionnelle.

## 7. Niveaux de decision

### Niveau operationnel

Acteurs :

- Magasinier ;
- Responsable stock.

Actions :

- enregistrer les entrees ;
- enregistrer les sorties ;
- consulter les produits ;
- faire les inventaires ;
- traiter les alertes ;
- suivre les previsions.

Pages concernees :

- Produits ;
- Inventaires ;
- Alertes ;
- Previsions ;
- Dashboard operationnel.

### Niveau tactique

Acteur :

- Responsable stock ;
- Chef d'agence selon l'organisation.

Objectif :

Suivre les risques, prioriser les reapprovisionnements et preparer les decisions de gestion.

Pages concernees :

- Alertes ;
- Previsions ;
- Synthese decisionnelle.

### Niveau strategique

Acteurs :

- Chef d'agence ;
- Directeur general.

Objectif :

Prendre du recul sur l'etat global du stock, les fournisseurs sensibles, les categories a risque et les donnees a presenter en reunion.

Pages concernees :

- Synthese decisionnelle ;
- Rapports ;
- Exports PDF / Excel.

## 8. Architecture fonctionnelle

Modules principaux :

- authentification ;
- gestion des utilisateurs ;
- gestion des roles et permissions ;
- catalogue produits ;
- categories ;
- fournisseurs ;
- mouvements de stock ;
- inventaires ;
- alertes ;
- previsions ;
- rapports ;
- synthese decisionnelle.

Organisation logique :

```text
Interface utilisateur
-> Controleurs
-> Services metier
-> Modeles Eloquent
-> Base de donnees
```

Services principaux :

- `StockService` : entree, sortie, ajustement, mise a jour du stock ;
- `AlertService` : creation, consultation, resolution automatique ou manuelle ;
- `ForecastService` : calcul des previsions ;
- `InventoryService` : validation des inventaires et mouvements d'ajustement ;
- `DashboardService` : indicateurs operationnels ;
- `AnalyticsCompilationService` : compilation decisionnelle ;
- `VisualizationService` : preparation des donnees de graphes ;
- `ReportService` : exports PDF et Excel.

## 9. Architecture des donnees

### Donnees operationnelles

Tables operationnelles :

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

Ces tables representent les operations courantes de l'application.

### Donnees decisionnelles

Tables analytiques :

- `analytics_runs` ;
- `analytics_product_kpis` ;
- `analytics_category_kpis` ;
- `analytics_supplier_kpis` ;
- `analytics_inventory_kpis`.

Ces tables representent une couche de synthese. Elles ne remplacent pas les donnees operationnelles, elles les condensent.

### Justification de la couche analytics

Le choix d'une couche `analytics_*` dans la meme base permet :

- de garder le MVP simple ;
- d'eviter une architecture trop lourde ;
- de separer les donnees operationnelles des donnees de decision ;
- de preparer une evolution future vers un vrai Data Warehouse ;
- de montrer une demarche BI credible.

## 10. Workflows principaux

### Creation d'un produit

```text
Gestionnaire
-> ajoute un produit
-> choisit categorie et fournisseur
-> renseigne les prix, SKU, unite et stock critique
-> le produit est cree avec stock initial 0
```

Points importants :

- SKU unique ;
- prix de vente superieur ou egal au prix d'achat ;
- produit actif par defaut ;
- fournisseur archive non selectionnable.

### Entree de stock

```text
Utilisateur autorise
-> selectionne un produit
-> saisit une quantite
-> StockService cree un mouvement ENTRY
-> le stock augmente
-> AlertService verifie les alertes
```

Impact :

- le stock peut repasser au-dessus du seuil critique ;
- une alerte ouverte peut etre resolue automatiquement.

### Sortie de stock

```text
Utilisateur autorise
-> selectionne un produit
-> saisit une quantite
-> le systeme verifie le stock disponible
-> si quantite > stock, operation refusee
-> sinon mouvement EXIT et stock diminue
-> verification des alertes
```

Impact :

- le stock negatif est interdit ;
- une alerte peut etre creee si le seuil critique est atteint.

### Inventaire

```text
Responsable stock
-> cree un inventaire
-> selectionne les produits
-> saisit les quantites reelles
-> valide l'inventaire
-> InventoryService calcule les ecarts
-> cree des mouvements ADJUSTMENT
-> synchronise le stock avec la realite physique
```

Importance :

L'inventaire permet de corriger l'ecart entre le stock theorique et le stock reel.

### Alertes

```text
Entree, sortie ou ajustement
-> verification du stock
-> creation d'alerte si stock critique
-> conservation d'une seule alerte active par produit
-> resolution si retour au-dessus du seuil
```

### Previsions

```text
Lecture des sorties sur 90 jours
-> calcul de la consommation moyenne journaliere
-> estimation des jours restants
-> estimation de la date de rupture
-> calcul du risque
-> calcul de la quantite recommandee
```

### Synthese decisionnelle

```text
Chef d'agence
-> clique sur Compiler les donnees
-> AnalyticsCompilationService consolide les donnees
-> creation d'un analytics_run
-> creation des KPI produits, categories, fournisseurs, inventaires
-> affichage d'une synthese exploitable
-> export possible en PDF ou Excel
```

## 11. Formules utilisees

### Consommation moyenne journaliere

```text
CMJ = Total des sorties sur 90 jours / 90
```

### Jours restants

```text
Jours restants = Stock actuel / CMJ
```

Si `CMJ = 0`, les jours restants ne sont pas estimables.

### Date de rupture

```text
Date de rupture = Date du jour + Jours restants
```

Si `CMJ = 0`, la date de rupture est `NULL`.

### Score de risque

```text
Si jours restants <= 7  -> risque = 100
Si jours restants <= 15 -> risque = 75
Si jours restants <= 30 -> risque = 50
Sinon                   -> risque = 25
```

Affichage UX :

- 25 : Faible ;
- 50 : Modere ;
- 75 : Eleve ;
- 100 : Critique.

### Quantite recommandee

Objectif : couvrir 60 jours de consommation.

```text
Quantite recommandee = (CMJ * 60) - Stock actuel
```

Si le resultat est negatif :

```text
Quantite recommandee = 0
```

### Produit sans consommation

Si :

```text
CMJ = 0
```

Alors :

- date de rupture : non estimable ;
- risk score : 25 ;
- quantite recommandee : 0.

Justification :

Le systeme ne dispose pas d'assez de donnees de consommation pour estimer une rupture.

## 12. Choix techniques a justifier

### Laravel

Laravel est adapte au projet car il offre :

- une structure MVC claire ;
- une gestion simple des migrations ;
- des services metier bien separables ;
- un systeme de validation robuste ;
- une compatibilite avec Breeze, Livewire, Spatie Permission, DomPDF et Excel.

### Livewire et Blade

Livewire permet de construire rapidement une application interactive sans separer completement backend et frontend.

Ce choix est pertinent pour un MVP academique, car il reduit la complexite tout en permettant une interface dynamique.

### Spatie Permission

Spatie Permission permet d'implementer clairement le RBAC.

Il soutient la logique des niveaux de decision :

- operationnel ;
- tactique ;
- strategique ;
- administration.

### ApexCharts

ApexCharts sert a produire des visualisations interactives et lisibles.

Les graphes ne sont pas la finalite du projet, mais ils rendent visibles les tendances, les risques et les priorites.

### SQLite pour le developpement

SQLite est utilise localement pour simplifier le developpement et les tests.

Pour une mise en production, le projet peut migrer vers MySQL ou PostgreSQL.

### Couche analytics dans la meme base

Ce choix evite une architecture trop lourde pour le MVP.

Il permet quand meme de montrer :

- la separation operationnel / decisionnel ;
- la compilation manuelle ;
- la conservation d'historique ;
- la logique de KPI ;
- l'evolution possible vers un Data Warehouse complet.

## 13. Choix de conception metier

### Stock negatif interdit

Le stock negatif est interdit car il rendrait les previsions et alertes incoherentes.

### Mouvements immutables

Un mouvement valide ne doit pas etre modifie.

Justification :

- conservation de l'historique ;
- tracabilite ;
- audit ;
- fiabilite des calculs.

### Suppression physique interdite

Les donnees sont archivees plutot que supprimees.

Justification :

- historique conservable ;
- coherence des mouvements ;
- traçabilite des decisions ;
- possibilite d'analyse future.

### Une seule alerte active par produit

Cette regle evite les doublons et rend l'interface plus lisible.

### Compilation manuelle de la synthese

Pour le MVP, la compilation est manuelle.

Justification :

- le chef d'agence choisit le moment de preparation ;
- la synthese correspond a une reunion ou une analyse precise ;
- le systeme reste simple et explicable.

## 14. Ce qu'il faut montrer dans la soutenance

Priorite 1 : la chaine decisionnelle.

```text
Produit
-> Mouvement
-> Alerte
-> Prevision
-> Dashboard
-> Synthese decisionnelle
```

Priorite 2 : les alertes.

- creation automatique ;
- une seule alerte ouverte par produit ;
- resolution automatique ;
- historique conserve ;
- separation nouvelles / consultees / resolues.

Priorite 3 : les previsions.

- periode de 90 jours ;
- CMJ ;
- jours restants ;
- date de rupture ;
- risque ;
- quantite recommandee.

Priorite 4 : la synthese decisionnelle.

- compilation manuelle ;
- tables analytics ;
- KPI produits, categories, fournisseurs, inventaires ;
- export PDF / Excel ;
- vision chef d'agence / DG.

Priorite 5 : les droits utilisateurs.

- magasinier : operations simples ;
- responsable stock : alertes, previsions, inventaires ;
- chef d'agence : synthese decisionnelle ;
- DG : rapports et vision strategique ;
- administrateur : gestion complete.

## 15. Ce qu'il ne faut pas trop mettre en avant

Il ne faut pas presenter StockFlow comme :

- un SaaS deja complet ;
- un vrai Data Warehouse separe ;
- un outil d'intelligence artificielle ;
- une application multi-entreprise ;
- un ERP complet ;
- un systeme de comptabilite ou facturation.

Il faut plutot dire :

```text
StockFlow est un MVP evolutif qui pose une base solide pour aller vers un SaaS et une architecture decisionnelle plus avancee.
```

## 16. Limites reconnues

Limites actuelles :

- application mono-entreprise ;
- donnees de demo limitees ;
- previsions basees sur une moyenne simple ;
- pas de modeles statistiques avances ;
- pas de Data Warehouse separe ;
- pas de planification automatique de compilation analytics ;
- graphes dependants de la qualite des donnees historiques.

Ces limites ne diminuent pas la valeur du projet si elles sont presentees comme des choix de MVP.

## 17. Evolutions possibles

### Evolution SaaS

- multi-tenant ;
- gestion de plusieurs entreprises ;
- plans d'abonnement ;
- super-admin SaaS ;
- separation logique ou physique des donnees ;
- facturation.

### Evolution Data Warehouse

- base analytique separee ;
- processus ETL/ELT ;
- historisation plus poussee ;
- dimensions et faits ;
- cubes ou agregats ;
- comparaison multi-periodes.

### Evolution predictive

- saisonnalite ;
- tendances mensuelles ;
- modeles statistiques ;
- machine learning ;
- detection d'anomalies ;
- recommandation automatique de commande.

### Evolution produit

- bons de commande ;
- reception fournisseur ;
- multi-entrepots ;
- valorisation avancee du stock ;
- notifications email ;
- API externe ;
- application mobile magasinier.

## 18. Plan possible du memoire

### Introduction

- contexte ;
- probleme de gestion de stock ;
- interet de l'aide a la decision ;
- problematique ;
- objectifs ;
- methode ;
- annonce du plan.

### Chapitre 1 : Cadre theorique

- systeme d'information ;
- gestion de stock ;
- Business Intelligence ;
- Data Warehouse / Data Mart ;
- SIAD ;
- KPI ;
- RBAC ;
- prevision de rupture.

### Chapitre 2 : Analyse et conception

- besoins fonctionnels ;
- acteurs ;
- niveaux de decision ;
- workflows ;
- diagrammes UML ;
- modele de donnees ;
- architecture applicative.

### Chapitre 3 : Realisation

- stack technique ;
- structure Laravel ;
- services metier ;
- modules realises ;
- alertes ;
- previsions ;
- synthese decisionnelle ;
- exports.

### Chapitre 4 : Tests, resultats et discussion

- tests fonctionnels ;
- tests des droits ;
- tests des workflows ;
- resultats obtenus ;
- limites ;
- perspectives.

### Conclusion

- rappel du probleme ;
- apports de StockFlow ;
- validation des objectifs ;
- ouvertures vers SaaS, Data Warehouse et prevision avancee.

## 19. Diagrammes a prevoir dans le document final

Diagrammes recommandes :

- diagramme de cas d'utilisation ;
- diagramme de classes ou modele conceptuel ;
- diagramme de base de donnees ;
- diagramme de sequence pour entree/sortie de stock ;
- diagramme de sequence pour generation d'alerte ;
- diagramme de sequence pour calcul de prevision ;
- diagramme de flux operationnel vers decisionnel ;
- architecture applicative ;
- schema de la couche analytics ;
- matrice des roles et permissions.

## 20. Arguments pour convaincre le jury

Arguments cles :

- le projet repond a un probleme concret ;
- les workflows sont coherents et complets ;
- les regles metier sont explicites ;
- les calculs sont simples, transparents et justifiables ;
- la separation des roles correspond aux niveaux de decision ;
- la couche analytics montre une demarche BI ;
- les exports rendent la synthese exploitable en reunion ;
- l'architecture est evolutive ;
- les limites sont reconnues et justifiees ;
- l'application peut evoluer vers un SaaS ou un vrai Data Warehouse.

Phrase forte possible :

```text
StockFlow ne se limite pas a enregistrer des stocks : il transforme les operations quotidiennes en indicateurs utiles a la decision.
```

## 21. Checklist avant redaction finale

- Verifier que les captures d'ecran correspondent a la derniere version de l'interface.
- Mettre a jour l'etat actuel de l'application si le design ou les modules changent.
- Generer les diagrammes propres en format A4.
- Stabiliser les donnees de demonstration.
- Recompiler la synthese decisionnelle avant les captures.
- Lister clairement les roles et permissions.
- Expliquer les formules de prevision.
- Expliquer pourquoi le MVP est mono-entreprise.
- Expliquer pourquoi la couche analytics est integree dans la meme base.
- Presenter les limites comme des choix de perimetre.
- Preparer une courte demonstration centree sur alertes, previsions et synthese.

