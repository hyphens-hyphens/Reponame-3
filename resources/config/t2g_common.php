<?php

return [
    'asset' => [
        'version' => ''
    ],
    'site'     => [
        'domains' => [], // example.com, example.net...
        'seo'     => [
            'title'            => '',
            'meta_description' => '',
            'meta_keyword'    => '',
            'meta_image'       => '',
        ],
        // Open Graph configs
        'og' => [
            'section' => 'Gaming',
            'tag' => ''
        ]
    ],
    'models'   => [
        'user_model_class'    => 'App\User',
        'payment_model_class' => 'T2G\Common\Models\Payment',
    ],
    'game_api' => [
        'base_url'         => env('GAME_API_BASE_URL'),
        'api_key'          => env('GAME_API_KEY'),
        'timeout'          => 10, // seconds
        'legacy'           => true,
        'is_mocked'        => env('GAME_API_MOCK', true),
        'maintenance_time' => [
            'start' => 1620, // in int format, see CCUController::getCCUPeakChartData
            'end'   => 1710,
        ],
    ],
    'payment'  => [
        'card_payment_partner'        => env(
            'CARD_PAYMENT_PARTNER',
            \T2G\Common\Contract\CardPaymentInterface::PARTNER_RECARD
        ),
        'card_payment_mocked'         => env('CARD_PAYMENT_API_MOCK', true),
        'card_payment_partner_pos2'   => env(
            'CARD_PAYMENT_PARTNER_POS2',
            \T2G\Common\Contract\CardPaymentInterface::PARTNER_NAPTHENHANH
        ),
        'recard'                      => [
            'merchant_id' => env('RECARD_MERCHANT_ID'),
            'secret_key'  => env('RECARD_SECRET_KEY'),
        ],
        'napthenhanh'                 => [
            'partner_id'  => env('NAPTHENHANH_PARTNER_ID'),
            'partner_key' => env('NAPTHENHANH_PARTNER_KEY'),
        ],
        'banking_account_dong_a'      => '',
        'banking_account_vietcombank' => '',
        // tỉ lệ quy đổi vàng từ VND
        'game_gold'                   => [
            'exchange_rate' => 1000, // $gold = round($money / {exchange_rate})
            'bonus_rate'    => 10, // $bonusGold = ceil($gold * {bonus_rate} / 100)
        ],
        // tỉ lệ chia lợi nhuận cho đối tác (%)
        'revenue_rate'                => [
            'recard'      => 32,
            'napthenhanh' => 32,
            'zing'        => 30,
        ],
        'skip_cashout_alert' => false
    ],
    'momo'     => [
        'mailbox' => 'momo_mailbox', // mailbox name as configured in webklex/laravel-imap package config file
    ],
    'discord'  => [
        'webhooks' => [
            // thông báo giao dịch từ email (MoMo), SMS webhook
            'payment_alert'  => env('DISCORD_PAYMENT_ALERT_WEBHOOK_URL'),
            // thông báo khi QTV add vàng từ admincp
            'add_gold'       => env('DISCORD_ADD_GOLD_WEBHOOK_URL'),
            'police'         => env('DISCORD_POLICE_WEBHOOK_URL'),
            'multiple_pc'    => env('DISCORD_MULTIPLE_PC_WEBHOOK_URL'),
            'multiple_login' => env('DISCORD_MULTIPLE_LOGIN_WEBHOOK_URL'),
        ],
    ],
    'kibana' => [
        'elasticsearch_config' => [
            'hosts' => [
                'localhost:9200'
            ],
            'retries' => 2,
            'handler' => \Elasticsearch\ClientBuilder::singleHandler()
        ],
        'index_suffix' => '_*'
    ]
];
