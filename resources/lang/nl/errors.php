<?php

// Error pages (resources/views/errors).

return [
    'code' => 'Fout :code',
    'home' => 'Terug naar home',
    'shop' => 'Naar de winkel',
    'contact' => 'Neem contact op',
    '404' => [
        'title' => 'Pagina niet gevonden',
        'message' => 'Deze pagina bestaat niet of is verplaatst. Misschien is het product niet meer beschikbaar.',
    ],
    '403' => [
        'title' => 'Geen toegang',
        'message' => 'Je hebt geen toestemming om deze pagina te bekijken.',
    ],
    '419' => [
        'title' => 'Pagina verlopen',
        'message' => 'Deze pagina stond te lang open. Ga terug, vernieuw de pagina en probeer het opnieuw.',
    ],
    '429' => [
        'title' => 'Te veel verzoeken',
        'message' => 'Je hebt in korte tijd meerdere verzoeken verstuurd. Wacht een minuut en probeer het opnieuw.',
    ],
    '500' => [
        'title' => 'Er ging iets mis',
        'message' => 'Er is bij ons een fout opgetreden. Probeer het zo opnieuw, of neem contact op als het blijft gebeuren.',
    ],
    '503' => [
        'title' => 'Zo terug',
        'message' => 'We voeren onderhoud uit. Probeer het over een paar minuten opnieuw.',
    ],
];
