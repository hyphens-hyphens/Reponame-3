<?php

return [
    'site' => [
        'domains' => [], // example.com, example.net...
        'seo' => [
            'meta_description' => '',
            'meta_keywords' => '',
            'meta_image' => '',
        ]
    ],
    'models' => [
        'user_model_class' => 'App\User',
        'payment_model_class' => 'T2G\Common\Models\Payment',
    ],
    'momo' => [
        'mailbox' => 'momo_mailbox'
    ],
    'discord' => [
        'webhooks' => [
            'payment_alert' => env('DISCORD_PAYMENT_ALERT_WEBHOOK_URL', ''),
            'add_gold' => env('DISCORD_ADD_GOLD_WEBHOOK_URL', ''),
        ]
    ],
    'env' => [
        'GOLD_EXCHANGE_RATE'            => env('GOLD_EXCHANGE_RATE', 1000),
        'GOLD_EXCHANGE_BONUS'           => env('GOLD_EXCHANGE_BONUS', 10),
        'REVENUE_RATE_CARD_RECARD'      => env('REVENUE_RATE_CARD_RECARD', 32),
        'REVENUE_RATE_CARD_NAPTHENHANH' => env('REVENUE_RATE_CARD_NAPTHENHANH', 32),
        'REVENUE_RATE_CARD_ZING'        => env('REVENUE_RATE_CARD_ZING', 30),
    ]

];
