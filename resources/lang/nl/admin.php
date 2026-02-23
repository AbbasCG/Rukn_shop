<?php

return [
    'sidebar' => [
        'brand' => 'Beheerpaneel',
        'sections' => [
            'menu' => 'Menu',
            'coming_soon' => 'Binnenkort',
        ],
        'items' => [
            'dashboard' => 'Dashboard',
            'products' => 'Producten',
            'categories' => 'Categorieën',
            'orders' => 'Bestellingen',
            'users' => 'Gebruikers',
            'settings' => 'Instellingen',
            'reports' => 'Rapporten',
            'logout' => 'Uitloggen',
        ],
        'tooltips' => [
            'close_sidebar' => 'Zijbalk sluiten',
        ],
    ],
    'footer' => [
        'logged_in_as' => 'Ingelogd als',
        'back_to_shop' => 'Terug naar winkel',
        'rights' => 'Alle rechten voorbehouden',
        'powered_by' => 'Mogelijk gemaakt door :name',
        'links' => [
            'privacy' => 'Privacybeleid',
            'terms' => 'Voorwaarden',
            'support' => 'Support',
            'docs' => 'Documentatie',
        ],
    ],
    'dashboard' => [
        'page_title' => 'Dashboard',
        'title' => 'Welkom terug, :name 👋',
        'cards' => [
            'total_products' => 'Totaal producten',
            'in_inventory' => 'In voorraad',
            'total_categories' => 'Totaal categorieën',
            'active_categories' => 'Actieve categorieën',
            'total_orders' => 'Totaal bestellingen',
            'all_time' => 'Aller tijden',
            'total_revenue' => 'Totale omzet',
            'from_paid_orders' => 'Van betaalde bestellingen',
        ],
        'sales' => [
            'title' => 'Verkoop laatste 12 maanden',
            'subtitle' => 'Omzetoverzicht en trends',
            'chart_placeholder' => 'Grafiek wordt hier weergegeven',
        ],
        'quick_actions' => [
            'title' => 'Snelle acties',
            'subtitle' => 'Veelgebruikte taken',
            'add_new_product' => 'Nieuw product toevoegen',
            'add_new_category' => 'Nieuwe categorie toevoegen',
            'add_new_user' => 'Nieuwe gebruiker toevoegen',
            'view_all_orders' => 'Alle bestellingen bekijken',
        ],
        'low_stock' => [
            'title' => 'Meldingen lage voorraad',
            'items_left' => ':count over',
            'all_well_stocked' => '✓ Alle producten zijn goed op voorraad',
        ],
        'recent_orders' => [
            'title' => 'Recente bestellingen',
            'subtitle' => 'Laatste klantbestellingen',
            'guest' => 'Gast',
            'view_action' => 'Bekijken',
            'empty' => 'Nog geen bestellingen',
            'table' => [
                'order_id' => 'Bestel-ID',
                'customer' => 'Klant',
                'total' => 'Totaal',
                'status' => 'Status',
                'date' => 'Datum',
                'action' => 'Actie',
            ],
        ],
        'statuses' => [
            'pending' => 'In afwachting',
            'paid' => 'Betaald',
            'shipped' => 'Verzonden',
            'completed' => 'Voltooid',
            'cancelled' => 'Geannuleerd',
        ],
    ],
];
