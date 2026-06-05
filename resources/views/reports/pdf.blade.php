<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <style>
            body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
            h1 { color: #4c1d95; font-size: 20px; margin: 0 0 4px; }
            p { margin: 0; }
            .brand { color: #6d28d9; font-size: 11px; font-weight: bold; margin-bottom: 10px; }
            .meta { color: #6b7280; margin-bottom: 18px; }
            table { border-collapse: collapse; width: 100%; }
            th { background: #ede9fe; color: #4c1d95; font-size: 9px; padding: 7px; text-align: left; }
            td { border-bottom: 1px solid #e5e7eb; padding: 7px; vertical-align: top; }
            .empty { color: #6b7280; padding: 16px; text-align: center; }
        </style>
    </head>
    <body>
        <p class="brand">StockFlow</p>
        <h1>{{ $title }}</h1>
        <p class="meta">{{ $description }}<br>Généré le {{ $generatedAt->format('d/m/Y H:i') }}</p>

        <table>
            <thead>
                <tr>
                    @foreach ($columns as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        @foreach (array_keys($columns) as $key)
                            <td>{{ $row[$key] ?? '' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="empty">Aucune donnee disponible.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>
