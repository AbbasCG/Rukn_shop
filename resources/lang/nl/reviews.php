<?php

return [
    'stars' => '{1} 1 ster|[2,*] :count sterren',
    'count' => '{1} 1 review|[2,*] :count reviews',

    'form' => [
        'title' => 'Beoordeel je aankoop',
        'subtitle' => 'Bestelling #:number. Vertel andere klanten wat je ervan vindt.',
        'not_delivered' => 'Je kunt je bestelling beoordelen zodra die is bezorgd.',
        'nothing_to_review' => 'Er valt in deze bestelling niets meer te beoordelen.',
        'rating' => 'Je beoordeling',
        'comment' => 'Je review (optioneel)',
        'comment_placeholder' => 'Wat vind je er goed aan? Hoe zijn de pasvorm en de kwaliteit?',
        'name' => 'Naam bij je review',
        'name_hint' => 'Je e-mailadres en bestelling worden nooit getoond.',
        'submit' => 'Review versturen',
        'moderation_note' => 'Reviews verschijnen in de winkel na een korte controle.',
        'thanks' => 'Bedankt voor je review! Hij verschijnt in de winkel zodra we hem hebben gecontroleerd.',
        'already_reviewed' => 'Je hebt dit product al beoordeeld.',
        'your_reviews' => 'Je reviews',
    ],

    'status' => [
        'pending' => 'Wacht op goedkeuring',
        'approved' => 'Gepubliceerd',
        'rejected' => 'Niet gepubliceerd',
    ],

    'product' => [
        'title' => 'Reviews van klanten',
        'based_on' => 'Op basis van :count',
        'verified' => 'Geverifieerde aankoop',
        'average' => 'Gemiddelde beoordeling :rating van 5',
    ],

    'order_cta' => [
        'title' => 'Hoe bevalt je bestelling?',
        'text' => 'Beoordeel de producten die je hebt ontvangen en help andere klanten kiezen.',
        'button' => 'Schrijf een review',
    ],

    'admin' => [
        'title' => 'Reviews',
        'subtitle' => 'Nieuwe reviews verschijnen pas in de winkel nadat je ze hebt goedgekeurd.',
        'filters' => ['pending' => 'Wachtend', 'approved' => 'Gepubliceerd', 'rejected' => 'Afgewezen'],
        'empty' => 'Geen reviews hier.',
        'product' => 'Product',
        'author' => 'Naam',
        'rating' => 'Beoordeling',
        'review' => 'Review',
        'order' => 'Bestelling',
        'date' => 'Datum',
        'no_comment' => '(alleen sterren)',
        'approve' => 'Publiceren',
        'reject' => 'Afwijzen',
        'delete' => 'Verwijderen',
        'delete_confirm' => 'Deze review definitief verwijderen?',
        'approved' => 'De review is nu zichtbaar in de winkel.',
        'rejected' => 'De review wordt niet getoond.',
        'deleted' => 'De review is verwijderd.',
    ],
];
