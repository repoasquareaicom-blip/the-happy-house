<?php
return [
    // Global Stripe mode
    'mode' => 'live', // change to 'live' on production

    'test' => [
        'secret_key' => 'sk_test_51T4JHF1iDMRIBrSlAiguWekwJezrc3hGv0TU5KTTmdN7FJwuowk7QU2YGI9tQmzRm2wkXnXtsIAscA2458bWSPJB00wDhTArsb',
        'publishable_key' => 'pk_test_51T4JHF1iDMRIBrSlceEfsmZRJboOnjTH6RhOyAsUTEWr2d5p5M4gTrNOnCCa6wV4ahz8I0ChDwRTMtJchRa3vqJO005FM0GxEh',
        'price_id'   => 'price_1QbMdA06kM66h4BbUww9AZWb',
        'base_url'   => 'http://localhost/staging_thh'
    ],

    'live' => [
        'secret_key' => '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',
        'publishable_key' => 'pk_live_51QPb6I06kM66h4Bbn7q5MJ2r5qO9DlgYHSpcJvHCi9DY0yc0FDR65xk28p1QKFH29ny1JL9jovmA10JD2rJnXtvc003eECAp30',
        'price_id'   => 'price_1T0XBb06kM66h4Bb3GwdeFy4',
        'base_url'   => 'https://thehappyhouse.au'
    ],
];
