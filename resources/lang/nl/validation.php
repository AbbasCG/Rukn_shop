<?php

/*
| Dutch validation messages. Without this file Laravel fell back to its
| built-in English messages on Dutch pages.
*/

return [
    'accepted' => ':Attribute moet worden geaccepteerd.',
    'array' => ':Attribute moet een lijst zijn.',
    'boolean' => ':Attribute moet ja of nee zijn.',
    'confirmed' => 'De bevestiging van :attribute komt niet overeen.',
    'current_password' => 'Het wachtwoord is onjuist.',
    'date' => ':Attribute is geen geldige datum.',
    'digits' => ':Attribute moet uit :digits cijfers bestaan.',
    'email' => ':Attribute moet een geldig e-mailadres zijn.',
    'exists' => 'De gekozen :attribute bestaat niet.',
    'file' => ':Attribute moet een bestand zijn.',
    'filled' => ':Attribute is verplicht.',
    'image' => ':Attribute moet een afbeelding zijn.',
    'in' => 'De gekozen :attribute is ongeldig.',
    'integer' => ':Attribute moet een geheel getal zijn.',
    'lowercase' => ':Attribute moet in kleine letters zijn.',
    'max' => [
        'array' => ':Attribute mag niet meer dan :max items bevatten.',
        'file' => ':Attribute mag niet groter zijn dan :max kilobytes.',
        'numeric' => ':Attribute mag niet groter zijn dan :max.',
        'string' => ':Attribute mag niet meer dan :max tekens bevatten.',
    ],
    'mimes' => ':Attribute moet een bestand zijn van het type: :values.',
    'min' => [
        'array' => ':Attribute moet minimaal :min items bevatten.',
        'file' => ':Attribute moet minimaal :min kilobytes zijn.',
        'numeric' => ':Attribute moet minimaal :min zijn.',
        'string' => ':Attribute moet minimaal :min tekens bevatten.',
    ],
    'numeric' => ':Attribute moet een getal zijn.',
    'password' => [
        'letters' => ':Attribute moet minimaal één letter bevatten.',
        'mixed' => ':Attribute moet minimaal één hoofdletter en één kleine letter bevatten.',
        'numbers' => ':Attribute moet minimaal één cijfer bevatten.',
        'symbols' => ':Attribute moet minimaal één symbool bevatten.',
        'uncompromised' => 'Dit :attribute is gelekt bij een datalek. Kies een ander wachtwoord.',
    ],
    'regex' => 'Het formaat van :attribute is ongeldig.',
    'required' => ':Attribute is verplicht.',
    'required_if' => ':Attribute is verplicht als :other :value is.',
    'required_with' => ':Attribute is verplicht als :values aanwezig is.',
    'same' => ':Attribute en :other moeten overeenkomen.',
    'size' => [
        'string' => ':Attribute moet :size tekens bevatten.',
    ],
    'string' => ':Attribute moet tekst zijn.',
    'unique' => ':Attribute is al in gebruik.',
    'uploaded' => 'Het uploaden van :attribute is mislukt.',
    'url' => ':Attribute moet een geldige URL zijn.',

    'custom' => [],

    'attributes' => [
        'name' => 'naam',
        'email' => 'e-mailadres',
        'password' => 'wachtwoord',
        'current_password' => 'huidig wachtwoord',
        'password_confirmation' => 'wachtwoordbevestiging',
        'phone' => 'telefoonnummer',
        'address' => 'adres',
        'address_line1' => 'adres',
        'address_line2' => 'adresregel 2',
        'postal_code' => 'postcode',
        'city' => 'plaats',
        'country' => 'land',
        'shipping_method' => 'verzendmethode',
        'payment_method' => 'betaalmethode',
        'quantity' => 'aantal',
        'avatar' => 'profielfoto',
        'subject' => 'onderwerp',
        'message' => 'bericht',
    ],
];
