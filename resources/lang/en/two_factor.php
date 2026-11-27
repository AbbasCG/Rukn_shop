<?php

return [
    'send_failed' => 'The code could not be emailed. Please try again in a moment.',

    'email' => [
        'subject' => ':code is your Rukn login code',
        'preheader' => 'Your code for the Rukn admin panel. It works for :minutes minutes.',
        'intro' => 'Enter this code to finish logging in to the Rukn admin panel:',
        'expires' => 'The code works for :minutes minutes and can be used once.',
        'not_you' => 'Did you not try to log in? Then someone knows your password: change it straight away from your profile page. Without this code they cannot get in.',
    ],

    'challenge' => [
        'title' => 'Check your email',
        'intro' => 'Your password is correct. We emailed you a 6-digit code (valid for :minutes minutes) to:',
        'code_label' => 'Code',
        'submit' => 'Log in',
        'resend' => 'Send a new code',
        'resent' => 'A new code has been sent.',
        'back' => 'Back to login',
        'spam_hint' => 'Nothing received? Check your spam folder, or send a new code after a minute.',
        'wrong' => 'This code is not correct.',
        'expired' => 'This code has expired. Send a new code.',
        'too_many' => 'Too many wrong codes. Send a new code.',
        'wait' => 'Please wait :seconds seconds before sending another code.',
        'start_again' => 'Too many codes requested. Please log in again.',
    ],

    'settings' => [
        'title' => 'Two-step verification',
        'subtitle' => 'Extra protection for the admin panel: after your password you also enter a code sent to your email.',
        'off' => 'Off',
        'on_since' => 'On since :date',
        'explain' => 'Codes go to :email. To check that they arrive, we first send you a code to confirm.',
        'enable' => 'Turn on',
        'code_sent' => 'We emailed you a code. Enter it below to turn on two-step verification.',
        'enter_code' => 'Enter the code we sent to :email.',
        'confirm' => 'Confirm and turn on',
        'cancel' => 'Cancel',
        'enabled' => 'Two-step verification is on. From your next login you will be asked for a code.',
        'password_to_disable' => 'Your password (to turn it off)',
        'disable' => 'Turn off',
        'disabled' => 'Two-step verification is off.',
        'too_many_sends' => 'Too many codes requested. Please try again later.',
    ],
];
