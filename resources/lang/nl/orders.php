<?php

return [
    'title' => 'Mijn Bestellingen',
    'subtitle' => 'Bekijk je recente aankopen en hun status.',

    'empty' => [
        'title' => 'Je hebt nog geen bestellingen geplaatst.',
        'subtitle' => 'Begin met winkelen om je bestellingen hier te zien.',
        'button' => 'Start met winkelen',
    ],

    'table' => [
        'order_number' => 'Bestelling #',
        'date' => 'Datum',
        'total' => 'Totaal',
        'status' => 'Status',
        'actions' => 'Acties',
    ],

    'status' => [
        'pending' => 'In behandeling',
        'paid' => 'Betaald',
        'processing' => 'Verwerking',
        'shipped' => 'Verzonden',
        'delivered' => 'Bezorgd',
        'cancelled' => 'Geannuleerd',
        'refunded' => 'Terugbetaald',
    ],

    'actions' => [
        'view_details' => 'Details bekijken',
        'back_to_orders' => 'Terug naar mijn bestellingen',
        'back_to_profile' => 'Terug naar profiel',
        'continue_shopping' => 'Verder winkelen',
    ],

    'details' => [
        'title' => 'Besteldetails',
        'summary' => 'Besteloverzicht',
        'shipping_info' => 'Verzendgegevens',
        'items' => 'Bestellingsitems',
        'status_tracking' => 'Bestelstatus',
        'notes' => 'Bestelopmerkingen',
    ],

    'summary' => [
        'subtotal' => 'Subtotaal',
        'shipping' => 'Verzending',
        'tax' => 'Belasting',
        'discount' => 'Korting',
        'total' => 'Totaal',
        'payment_method' => 'Betalingsmethode',
        'payment_status' => 'Betalingsstatus',
    ],

    'shipping' => [
        'name' => 'Naam',
        'address' => 'Adres',
        'postal_code' => 'Postcode',
        'city' => 'Plaats',
        'country' => 'Land',
        'phone' => 'Telefoonnummer',
    ],

    'items' => [
        'product' => 'Product',
        'sku' => 'Artikelcode',
        'quantity' => 'Hoeveelheid',
        'price_per_unit' => 'Prijs per stuk',
        'item_total' => 'Totaal item',
        'no_items' => 'Geen items gevonden in deze bestelling.',
    ],

    'tracking' => [
        'title' => 'Bestelstatus',
        'live_tracking' => 'Live tracking',
        'current_status' => 'Huidige status',
        'in_progress' => 'In behandeling',
        'update_message' => 'We zullen je op de hoogte brengen wanneer je bestelling naar de volgende stap gaat.',
        'steps' => [
            'pending' => 'In behandeling',
            'paid' => 'Betaald',
            'processing' => 'Verwerking',
            'shipped' => 'Verzonden',
            'delivered' => 'Bezorgd',
        ],
    ],

    'confirmation' => [
        'thank_you' => 'Bedankt voor je bestelling!',
        'received' => 'Je bestelling is ontvangen en wordt nu verwerkt.',
        'summary' => 'Besteloverzicht',
        'order_number' => 'Bestelling #',
    ],
];
