<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class PermissionCatalog
{
    /**
     * @return array<int, array{key: string, label: string, permissions: Collection<int, Permission>}>
     */
    public static function group(Collection $permissions): array
    {
        return $permissions
            ->groupBy(fn (Permission $permission): string => Str::before($permission->name, '.'))
            ->map(fn (Collection $items, string $group): array => [
                'key' => $group,
                'label' => self::groupLabel($group),
                'permissions' => $items
                    ->sortBy(fn (Permission $permission): int => self::permissionOrder($permission->name))
                    ->values(),
            ])
            ->sortBy(fn (array $group): int => self::groupOrder($group['key']))
            ->values()
            ->all();
    }

    public static function groupLabel(string $group): string
    {
        return match ($group) {
            'alerts' => 'Alertes',
            'analytics' => 'Synthèse décisionnelle',
            'categories' => 'Catégories',
            'forecasts' => 'Prévisions',
            'inventories' => 'Inventaires',
            'products' => 'Produits',
            'reports' => 'Rapports',
            'roles' => 'Rôles et droits',
            'stock' => 'Opérations de stock',
            'suppliers' => 'Fournisseurs',
            'users' => 'Utilisateurs',
            default => Str::headline($group),
        };
    }

    public static function permissionLabel(string $permission): string
    {
        return match ($permission) {
            'alerts.resolve' => 'Résoudre une alerte',
            'alerts.view' => 'Consulter les alertes',
            'analytics.compile' => 'Compiler la synthèse décisionnelle',
            'analytics.export' => 'Exporter la synthèse décisionnelle',
            'analytics.view' => 'Consulter la synthèse décisionnelle',
            'categories.create' => 'Créer une catégorie',
            'categories.update' => 'Modifier une catégorie',
            'categories.view' => 'Consulter les catégories',
            'forecasts.view' => 'Consulter les prévisions',
            'inventories.create' => 'Créer un inventaire',
            'inventories.validate' => 'Valider un inventaire',
            'inventories.view' => 'Consulter les inventaires',
            'products.archive' => 'Archiver un produit',
            'products.create' => 'Créer un produit',
            'products.update' => 'Modifier un produit',
            'products.view' => 'Consulter les produits',
            'reports.export' => 'Exporter les rapports',
            'reports.view' => 'Consulter les rapports',
            'roles.manage' => 'Créer et modifier les rôles',
            'roles.view' => 'Consulter les rôles',
            'stock.adjustment' => 'Ajuster manuellement le stock',
            'stock.entry' => 'Enregistrer une entrée de stock',
            'stock.exit' => 'Enregistrer une sortie de stock',
            'stock.view' => 'Consulter le stock',
            'suppliers.create' => 'Créer un fournisseur',
            'suppliers.update' => 'Modifier un fournisseur',
            'suppliers.view' => 'Consulter les fournisseurs',
            'users.create' => 'Créer un utilisateur',
            'users.disable' => 'Désactiver un utilisateur',
            'users.update' => 'Modifier un utilisateur',
            'users.view' => 'Consulter les utilisateurs',
            default => Str::headline(str_replace('.', ' ', $permission)),
        };
    }

    public static function permissionDescription(string $permission): string
    {
        return match ($permission) {
            'alerts.resolve' => 'Marquer une alerte comme traitée depuis son détail.',
            'alerts.view' => 'Voir la liste et le détail des alertes de stock.',
            'analytics.compile' => 'Transformer les données opérationnelles en indicateurs consolidés.',
            'analytics.export' => 'Télécharger la synthèse consolidée en PDF ou Excel.',
            'analytics.view' => 'Accéder au tableau décisionnel sans détail opérationnel.',
            'categories.create' => 'Ajouter une nouvelle famille de produits.',
            'categories.update' => 'Modifier les informations d une catégorie existante.',
            'categories.view' => 'Accéder à la liste des catégories.',
            'forecasts.view' => 'Voir les risques de rupture et les quantités conseillées.',
            'inventories.create' => 'Démarrer un comptage physique du stock.',
            'inventories.validate' => 'Confirmer un inventaire et appliquer les écarts au stock.',
            'inventories.view' => 'Voir les inventaires et leurs écarts.',
            'products.archive' => 'Retirer un produit du catalogue actif.',
            'products.create' => 'Ajouter un produit au catalogue.',
            'products.update' => 'Modifier la fiche d un produit actif.',
            'products.view' => 'Voir la liste, les fiches produits et leur historique.',
            'reports.export' => 'Télécharger les rapports en PDF ou Excel.',
            'reports.view' => 'Accéder à la page des rapports.',
            'roles.manage' => 'Créer des rôles et changer leurs droits.',
            'roles.view' => 'Voir les rôles configurés dans l application.',
            'stock.adjustment' => 'Corriger le stock depuis une fiche produit.',
            'stock.entry' => 'Ajouter des quantités reçues au stock.',
            'stock.exit' => 'Retirer des quantités consommées ou livrées.',
            'stock.view' => 'Voir les niveaux de stock et mouvements associés.',
            'suppliers.create' => 'Ajouter un fournisseur.',
            'suppliers.update' => 'Modifier les coordonnées d un fournisseur.',
            'suppliers.view' => 'Voir la liste des fournisseurs.',
            'users.create' => 'Créer un compte interne.',
            'users.disable' => 'Bloquer ou réactiver l accès d un compte.',
            'users.update' => 'Modifier le profil et le rôle d un utilisateur.',
            'users.view' => 'Voir la liste des utilisateurs.',
            default => 'Autorisation interne de l application.',
        };
    }

    private static function groupOrder(string $group): int
    {
        $position = array_search($group, [
            'products',
            'stock',
            'inventories',
            'alerts',
            'forecasts',
            'analytics',
            'reports',
            'categories',
            'suppliers',
            'users',
            'roles',
        ], true);

        return $position === false ? 999 : $position;
    }

    private static function permissionOrder(string $permission): int
    {
        $action = Str::after($permission, '.');

        $position = array_search($action, [
            'view',
            'create',
            'compile',
            'entry',
            'exit',
            'adjustment',
            'update',
            'validate',
            'archive',
            'disable',
            'resolve',
            'export',
            'manage',
        ], true);

        return $position === false ? 999 : $position;
    }
}
