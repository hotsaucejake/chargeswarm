<?php

return [

    'models' => [
        'subscription' => \Rennokki\Chargeswarm\Models\Subscription::class,
        'subscriptionUsage' => \Rennokki\Chargeswarm\Models\SubscriptionUsage::class,
    ],

    'site' => env('CHARGEBEE_SITE', ''),
    'key' => env('CHARGEBEE_KEY', ''),
    'gateway' => env('CHARGEBEE_GATEWAY', ''),

];
