<?php

return [
    'send_failed' => 'De code kon niet worden gemaild. Probeer het zo opnieuw.',

    'email' => [
        'subject' => ':code is je Rukn-inlogcode',
        'preheader' => 'Je code voor het Rukn-beheer. Hij werkt :minutes minuten.',
        'intro' => 'Vul deze code in om het inloggen op het Rukn-beheer af te ronden:',
        'expires' => 'De code werkt :minutes minuten en is één keer te gebruiken.',
        'not_you' => 'Probeerde jij niet in te loggen? Dan kent iemand je wachtwoord: wijzig het meteen via je profielpagina. Zonder deze code komt diegene niet binnen.',
    ],

    'challenge' => [
        'title' => 'Kijk in je e-mail',
        'intro' => 'Je wachtwoord klopt. We hebben een code van 6 cijfers (:minutes minuten geldig) gemaild naar:',
        'code_label' => 'Code',
        'submit' => 'Inloggen',
        'resend' => 'Nieuwe code sturen',
        'resent' => 'Er is een nieuwe code verstuurd.',
        'back' => 'Terug naar inloggen',
        'spam_hint' => 'Niets ontvangen? Kijk in je spammap, of stuur na een minuut een nieuwe code.',
        'wrong' => 'Deze code klopt niet.',
        'expired' => 'Deze code is verlopen. Stuur een nieuwe code.',
        'too_many' => 'Te vaak een verkeerde code. Stuur een nieuwe code.',
        'wait' => 'Wacht nog :seconds seconden voordat je een nieuwe code stuurt.',
        'start_again' => 'Te veel codes aangevraagd. Log opnieuw in.',
    ],

    'settings' => [
        'title' => 'Tweestapsverificatie',
        'subtitle' => 'Extra beveiliging voor het beheer: na je wachtwoord vul je ook een code in die naar je e-mail wordt gestuurd.',
        'off' => 'Uit',
        'on_since' => 'Aan sinds :date',
        'explain' => 'Codes gaan naar :email. Om te controleren dat ze aankomen, sturen we eerst een code ter bevestiging.',
        'enable' => 'Aanzetten',
        'code_sent' => 'We hebben je een code gemaild. Vul hem hieronder in om tweestapsverificatie aan te zetten.',
        'enter_code' => 'Vul de code in die we naar :email hebben gestuurd.',
        'confirm' => 'Bevestigen en aanzetten',
        'cancel' => 'Annuleren',
        'enabled' => 'Tweestapsverificatie staat aan. Vanaf je volgende login vragen we om een code.',
        'password_to_disable' => 'Je wachtwoord (om het uit te zetten)',
        'disable' => 'Uitzetten',
        'disabled' => 'Tweestapsverificatie staat uit.',
        'too_many_sends' => 'Te veel codes aangevraagd. Probeer het later opnieuw.',
    ],
];
