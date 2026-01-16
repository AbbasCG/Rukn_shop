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
];
