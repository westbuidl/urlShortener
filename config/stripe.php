
<?php

use Stripe\StripeClient;
return[


'stripe_pk' => env('STRIPE_PK'),
'stripe_sk' => env('STRIPE_SK'),

];

$stripe_config = [
    'stripe_pk' => config('stripe.stripe_pk'),
    'stripe_sk' => config('stripe.stripe_sk'),
];
$stripe = new StripeClient($stripe_config);