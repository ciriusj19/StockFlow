<?php

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$basePath = dirname(__DIR__);
$htmlPath = $basePath.'/SCHEMA_BASE_DONNEES_VISUEL.html';
$pdfPath = $basePath.'/SCHEMA_BASE_DONNEES_VISUEL.pdf';

$businessTables = [
    'users' => 'Utilisateurs',
    'categories' => 'Categories',
    'suppliers' => 'Fournisseurs',
    'products' => 'Produits',
    'stock_movements' => 'Mouvements de stock',
    'inventories' => 'Inventaires',
    'inventory_items' => 'Lignes d inventaire',
    'alerts' => 'Alertes',
    'forecasts' => 'Previsions',
    'roles' => 'Roles',
    'permissions' => 'Permissions',
    'model_has_roles' => 'Attribution roles',
    'model_has_permissions' => 'Permissions directes',
    'role_has_permissions' => 'Permissions par role',
];

$tableNotes = [
    'users' => 'Comptes applicatifs et statut d activation.',
    'categories' => 'Referentiel de classement des produits.',
    'suppliers' => 'Referentiel des fournisseurs.',
    'products' => 'Catalogue produit et stock courant.',
    'stock_movements' => 'Journal des entrees, sorties et ajustements.',
    'inventories' => 'Sessions de comptage physique.',
    'inventory_items' => 'Produits comptes dans un inventaire, avec ecarts.',
    'alerts' => 'Historique des alertes de seuil critique.',
    'forecasts' => 'Historique des calculs de rupture et recommandations.',
    'roles' => 'Roles Spatie Permission.',
    'permissions' => 'Permissions Spatie Permission.',
    'model_has_roles' => 'Pivot entre utilisateurs et roles.',
    'model_has_permissions' => 'Pivot entre utilisateurs et permissions directes.',
    'role_has_permissions' => 'Pivot entre roles et permissions.',
];

$technicalTables = [
    'sessions',
    'cache',
    'cache_locks',
    'jobs',
    'job_batches',
    'failed_jobs',
    'password_reset_tokens',
];

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function q(string $identifier): string
{
    return '"'.str_replace('"', '""', $identifier).'"';
}

function columnsFor(string $table): array
{
    return DB::select('PRAGMA table_info('.q($table).')');
}

function foreignKeysFor(string $table): array
{
    $keys = [];

    foreach (DB::select('PRAGMA foreign_key_list('.q($table).')') as $key) {
        $keys[$key->from] = [
            'table' => $key->table,
            'column' => $key->to,
        ];
    }

    return $keys;
}

function node(string $table, string $label = ''): string
{
    $label = $label ?: strtoupper($table);

    return '<div class="node"><strong>'.e(strtoupper($table)).'</strong><span>'.e($label).'</span></div>';
}

function arrow(string $label): string
{
    return '<div class="arrow"><span>'.e($label).'</span></div>';
}

function diagramRow(array $cells): string
{
    return '<div class="diagram-row">'.implode('', $cells).'</div>';
}

$diagramRows = [
    diagramRow([
        node('categories', 'classe'),
        arrow('1,N'),
        node('products', 'produit central'),
        arrow('N,1'),
        node('suppliers', 'fournit'),
    ]),
    diagramRow([
        node('products'),
        arrow('1,N'),
        node('stock_movements', 'journal stock'),
        arrow('N,1'),
        node('users', 'responsable'),
    ]),
    diagramRow([
        node('products'),
        arrow('1,N'),
        node('alerts', 'seuil critique'),
        arrow(' '),
        node('forecasts', 'previsions produit'),
    ]),
    diagramRow([
        node('users'),
        arrow('1,N'),
        node('inventories', 'comptage'),
        arrow('1,N'),
        node('inventory_items', 'lignes'),
        arrow('N,1'),
        node('products'),
    ]),
    diagramRow([
        node('users'),
        arrow('N,N'),
        node('model_has_roles', 'pivot'),
        arrow('N,N'),
        node('roles'),
        arrow('N,N'),
        node('role_has_permissions', 'pivot'),
        arrow('N,N'),
        node('permissions'),
    ]),
    diagramRow([
        node('users'),
        arrow('N,N'),
        node('model_has_permissions', 'pivot'),
        arrow('N,N'),
        node('permissions'),
    ]),
];

$dictionaryHtml = '';

foreach ($businessTables as $table => $label) {
    $columns = columnsFor($table);
    $foreignKeys = foreignKeysFor($table);

    $rows = '';
    foreach ($columns as $column) {
        $constraints = [];

        if ((int) $column->pk === 1) {
            $constraints[] = '<span class="badge pk">PK</span>';
        }

        if (array_key_exists($column->name, $foreignKeys)) {
            $target = $foreignKeys[$column->name];
            $constraints[] = '<span class="badge fk">FK '.e($target['table']).'.'.e($target['column']).'</span>';
        }

        if ((int) $column->notnull === 1) {
            $constraints[] = '<span class="badge nn">NOT NULL</span>';
        }

        $rows .= '<tr>'
            .'<td class="field">'.e($column->name).'</td>'
            .'<td>'.e($column->type ?: '-').'</td>'
            .'<td>'.($constraints ? implode(' ', $constraints) : '<span class="muted">-</span>').'</td>'
            .'<td>'.e($column->dflt_value ?? '-').'</td>'
            .'</tr>';
    }

    $dictionaryHtml .= <<<HTML
        <section class="table-card">
            <div class="table-card-title">
                <h3>{$table}</h3>
                <p>{$label} - {$tableNotes[$table]}</p>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Champ</th>
                        <th>Type</th>
                        <th>Cles / contraintes</th>
                        <th>Defaut</th>
                    </tr>
                </thead>
                <tbody>{$rows}</tbody>
            </table>
        </section>
    HTML;
}

$technicalList = '<ul>';
foreach ($technicalTables as $table) {
    $technicalList .= '<li><strong>'.e($table).'</strong> : table technique Laravel.</li>';
}
$technicalList .= '</ul>';

$diagramHtml = implode('', $diagramRows);

$html = <<<HTML
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Schema de base de donnees - StockFlow</title>
    <style>
        @page { size: A4 landscape; margin: 18mm; }
        body {
            color: #111827;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12px;
            line-height: 1.45;
        }
        h1, h2, h3, p { margin: 0; }
        h1 {
            color: #2e1065;
            font-size: 28px;
            margin-bottom: 6px;
        }
        h2 {
            color: #2e1065;
            font-size: 20px;
            margin: 0 0 10px;
        }
        h3 { font-size: 15px; }
        .subtitle {
            color: #4b5563;
            font-size: 13px;
            margin-bottom: 18px;
        }
        .diagram-page {
            page-break-after: always;
        }
        .diagram-panel {
            border: 2px solid #ddd6fe;
            border-radius: 14px;
            padding: 14px;
        }
        .diagram-note {
            background: #f5f3ff;
            border: 1px solid #ddd6fe;
            border-radius: 8px;
            color: #4c1d95;
            font-size: 12px;
            margin-bottom: 12px;
            padding: 8px 10px;
        }
        .diagram-row {
            display: table;
            margin: 0 0 12px;
            table-layout: fixed;
            width: 100%;
        }
        .node,
        .arrow {
            display: table-cell;
            vertical-align: middle;
        }
        .node {
            background: #ffffff;
            border: 1.5px solid #7c3aed;
            border-radius: 10px;
            box-shadow: 0 1px 0 #ede9fe;
            height: 58px;
            padding: 8px 10px;
            text-align: center;
            width: 17%;
        }
        .node strong {
            color: #111827;
            display: block;
            font-size: 12px;
            letter-spacing: .02em;
        }
        .node span {
            color: #6b7280;
            display: block;
            font-size: 10px;
            margin-top: 2px;
        }
        .arrow {
            color: #6d28d9;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
            width: 7%;
        }
        .arrow span:before,
        .arrow span:after {
            content: "";
            display: inline-block;
            border-top: 2px solid #8b5cf6;
            margin: 0 5px 3px;
            width: 18px;
        }
        .nn-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            margin-top: 14px;
            padding: 12px 14px;
        }
        .nn-box ul {
            margin: 8px 0 0 18px;
            padding: 0;
        }
        .nn-box li { margin-bottom: 4px; }
        .page-break {
            page-break-before: always;
        }
        .table-card {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            margin-bottom: 14px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .table-card-title {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 9px 11px;
        }
        .table-card-title p {
            color: #6b7280;
            font-size: 11px;
            margin-top: 2px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th {
            background: #f3f4f6;
            color: #374151;
            font-size: 10px;
            padding: 7px 9px;
            text-align: left;
            text-transform: uppercase;
        }
        td {
            border-top: 1px solid #f3f4f6;
            padding: 7px 9px;
            vertical-align: top;
        }
        .field {
            color: #111827;
            font-weight: 700;
        }
        .badge {
            border-radius: 999px;
            display: inline-block;
            font-size: 9px;
            font-weight: 700;
            margin: 0 4px 4px 0;
            padding: 2px 7px;
            white-space: nowrap;
        }
        .pk { background: #ede9fe; color: #5b21b6; }
        .fk { background: #dbeafe; color: #1d4ed8; }
        .nn { background: #ecfdf5; color: #047857; }
        .muted { color: #9ca3af; }
    </style>
</head>
<body>
    <section class="diagram-page">
        <h1>Schema de base de donnees - StockFlow</h1>
        <p class="subtitle">Vue simplifiee : uniquement les tables et les liaisons. Les champs sont detailles apres le diagramme.</p>

        <div class="diagram-panel">
            <div class="diagram-note">
                Lecture : les boites sont les tables, les connecteurs indiquent la cardinalite principale. Les pivots Spatie et les tables associatives metier sont affichees comme des tables a part entiere.
            </div>
            {$diagramHtml}
        </div>

        <div class="nn-box">
            <h2>Relations N,N a retenir</h2>
            <ul>
                <li><strong>users N,N roles</strong> via <code>model_has_roles</code>.</li>
                <li><strong>roles N,N permissions</strong> via <code>role_has_permissions</code>.</li>
                <li><strong>users N,N permissions</strong> via <code>model_has_permissions</code>.</li>
                <li><strong>inventories N,N products</strong> via <code>inventory_items</code>.</li>
                <li><strong>products N,N users</strong> via <code>stock_movements</code>, relation historique/evenementielle.</li>
            </ul>
        </div>
    </section>

    <section>
        <h2>Dictionnaire des tables</h2>
        {$dictionaryHtml}

        <h2>Tables techniques Laravel</h2>
        {$technicalList}
    </section>
</body>
</html>
HTML;

file_put_contents($htmlPath, $html);

$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('a4', 'landscape');
$dompdf->render();

file_put_contents($pdfPath, $dompdf->output());

echo "HTML: {$htmlPath}".PHP_EOL;
echo "PDF: {$pdfPath}".PHP_EOL;
