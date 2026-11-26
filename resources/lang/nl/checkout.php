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
            'flat' => 'Standaardverzending',
        ],
        'payment_method' => 'Betaalmethode',
        'payment_options' => [
            'cod' => 'Betalen bij bezorging',
            'mollie' => 'Online betalen',
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
        'stock_changed' => 'Van :product zijn er nog maar :count op voorraad. Pas je winkelwagen aan.',
        'unavailable' => ':product is niet meer beschikbaar. Verwijder het uit je winkelwagen.',
    ],

    'confirmation' => [
        'view_order' => 'Bekijk mijn bestelling',
        'continue_shopping' => 'Verder winkelen',
        'placed_with_number' => 'Je bestelling :number is succesvol geplaatst.',
    ],
    'have_account' => 'Heb je een account? Inloggen',
    'payment' => [
        'description' => 'Bestelling #:number bij Rukn',
        'start_failed' => 'De online betaling kon niet worden gestart. Je winkelwagen is ongewijzigd; probeer het opnieuw of kies een andere betaalmethode.',
        'online' => 'Online betalen',
        'online_desc' => 'iDEAL, creditcard, Bancontact en meer. Je kiest op de beveiligde pagina van Mollie.',
        'cod' => 'Betalen bij bezorging',
        'cod_desc' => 'Je betaalt wanneer je bestelling wordt bezorgd.',
        'pending_title' => 'We controleren je betaling…',
        'pending_text' => 'Je bank bevestigt de betaling. Deze pagina ververst vanzelf.',
        'failed_title' => 'Betaling niet voltooid',
        'failed_text' => 'De betaling is geannuleerd of mislukt. Je bestelling blijft gereserveerd; je kunt het opnieuw proberen.',
        'retry' => 'Opnieuw betalen',
        'expired_title' => 'Bestelling geannuleerd',
        'expired_text' => 'De betaling is niet op tijd afgerond, daarom is de bestelling geannuleerd. Er is niets afgeschreven.',
        'back_to_shop' => 'Terug naar de winkel',
        'paid_online' => 'Online betaald',
    ],
];
