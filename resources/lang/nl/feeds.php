<?php

return [
    'channel_description' => 'Producten van :shop',
    'admin' => [
        'title' => 'Productfeeds',
        'subtitle' => 'Links met al je producten voor Google Shopping en de Instagram-/Facebook-shop. De platforms halen ze zelf op, dus prijzen, kortingen en voorraad blijven actueel.',
        'links' => 'Feedlinks (één per taal)',
        'copy' => 'Kopiëren',
        'open' => 'Openen',
        'count' => ':count producten staan in de feed (producten in maten/kleuren één keer per maat/kleur).',
        'without_photo' => ':count actieve producten hebben geen foto en worden overgeslagen (beide platforms vereisen er een):',
        'google_steps' => [
            'Ga naar merchants.google.com en maak een gratis account voor je winkel (land: Nederland, valuta: EUR).',
            'Verifieer en claim je website (rukn.nl) onder Instellingen → Bedrijfsgegevens.',
            'Producten → Producten toevoegen → Producten uit een bestand → "Bestand via een link toevoegen" (geplande ophaalactie).',
            'Plak de Nederlandse feedlink en kies dagelijks.',
            'Controleer onder Verzending de levertijd; de feed stuurt de verzendkosten naar Nederland al mee.',
            'Kijk na de eerste ophaalactie bij Producten onder "Aandacht vereist" of er artikelen zijn afgekeurd.',
        ],
        'meta_steps' => [
            'Ga naar business.facebook.com → Commerce Manager en maak een catalogus (type: e-commerce).',
            'Catalogus → Gegevensbronnen → Artikelen toevoegen → Gegevensfeed → "Een URL gebruiken".',
            'Plak een feedlink (Nederlands, of de taal van je volgers), kies valuta EUR en elke dag bijwerken.',
            'Koppel de catalogus aan je Instagram-account via Commerce Manager → Instellingen en zet productlabels aan in Instagram.',
            'Meta beoordeelt de winkel eerst; na goedkeuring kun je producten taggen in posts en stories.',
        ],
        'notes' => 'Prijzen zijn inclusief btw. Kortingen gaan mee met hun datums. Verborgen producten staan er nooit in. Er wordt geen geslacht of leeftijdsgroep meegestuurd: voeg die toe in Merchant Center als Google erom vraagt voor kleding.',
    ],
];
