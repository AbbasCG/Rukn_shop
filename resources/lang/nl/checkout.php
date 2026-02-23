<?php

return [
    'title' => 'Afrekenen',
    'subtitle' => 'Vul je gegevens in om de bestelling te plaatsen.',

    'form' => [
        'name' => 'Naam',
        'email' => 'E-mailadres',
        'phone' => 'Telefoonnummer',
        'country' => 'Land',
        'address1' => 'Adresregel 1',
        'address2' => 'Adresregel 2',
        'postal_code' => 'Postcode',
        'city' => 'Plaats',
        'shipping_method' => 'Verzendmethode',
        'shipping_options' => [
            'flat' => 'Vast tarief (gratis)',
        ],
        'payment_method' => 'Betaalmethode',
        'payment_options' => [
            'cod' => 'Betalen bij bezorging',
            'mock' => 'Testbetaling',
        ],
        'submit' => 'Bestelling plaatsen',
    ],

    'summary' => [
        'title' => 'Bestellingsoverzicht',
        'subtotal' => 'Subtotaal',
        'shipping' => 'Verzending',
        'total' => 'Totaal',
        'back_to_cart' => 'Terug naar winkelwagen',
    ],

    'flash' => [
        'empty_cart' => 'Je winkelwagen is leeg.',
        'failed' => 'Het plaatsen van de bestelling is mislukt. Probeer het opnieuw.',
    ],

    'confirmation' => [
        'view_order' => 'Bekijk mijn bestelling',
        'continue_shopping' => 'Verder winkelen',
        'placed_with_number' => 'Je bestelling :number is succesvol geplaatst.',
        'whatsapp_button' => 'Bestelling sturen via WhatsApp',
        'whatsapp' => [
            'greeting' => 'Hallo! Hierbij mijn bestelgegevens:',
            'order_number' => 'Bestelnummer: :number',
            'customer_name' => 'Naam klant: :value',
            'customer_email' => 'E-mail klant: :value',
            'customer_phone' => 'Telefoon klant: :value',
            'delivery_address' => 'Bezorgadres: :value',
            'items_title' => 'Artikelen:',
            'item_line' => '- :name | Aantal: :qty | Prijs: :unit | Subtotaal: :subtotal',
            'total' => 'Totaal: :value',
            'payment_method' => 'Betaalmethode: :value',
            'order_link_label' => 'Link naar bestelling:',
        ],
    ],
];
