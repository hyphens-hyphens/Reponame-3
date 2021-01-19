<?php

return [
    'asset' => [
        'version' => '',
        'base_url' => env('ASSET_BASE_URL'),
    ],
    'site'     => [
        'domains' => [], // example.com, example.net...
        'seo' => [
            'title'            => '',
            'meta_description' => '',
            'meta_keyword'     => '',
            'meta_image'       => '',
        ],
        // Open Graph configs
        'og' => [
            'section' => 'Gaming',
            'tag' => ''
        ],
        'front_page_forbidden' => false,
        'front_page_forbidden_except_uris' => ['/', '/thoat'],
        'front_page_forbidden_redirect_url' => ['front.landing', []],
    ],
    'models'   => [
        'user_model_class'    => 'App\User',
        'payment_model_class' => 'T2G\Common\Models\Payment',
        'post_model_class' => 'T2G\Common\Models\Post',
    ],
    'game_api' => [
        'api_client_classname' => \T2G\Common\Services\JXApiClient::class,
        'base_url'             => env('GAME_API_BASE_URL'),
        'api_key'              => env('GAME_API_KEY'),
        'timeout'              => 10, // seconds
        'legacy'               => true,
        'is_mocked'            => env('GAME_API_MOCK', true),
        'ccu_tick_interval'    => 3000, // miliseconds
        'maintenance_time'     => [
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
            'payment_alert'         => env('DISCORD_PAYMENT_ALERT_WEBHOOK_URL'),
            // thông báo khi QTV add vàng từ admincp
            'add_gold'              => env('DISCORD_ADD_GOLD_WEBHOOK_URL'),
            'police'                => env('DISCORD_POLICE_WEBHOOK_URL'),
            'multiple_pc'           => env('DISCORD_MULTIPLE_PC_WEBHOOK_URL'),
            'multiple_login'        => env('DISCORD_MULTIPLE_LOGIN_WEBHOOK_URL'),
            'kimyen'                => env('DISCORD_KIMYEN_WEBHOOK_URL'),
            'monitor_gold_trading'  => env('DISCORD_MONITOR_GOLD_TRADING_WEBHOOK_URL'),
            'monitor_gold'          => env('DISCORD_MONITOR_GOLD_WEBHOOK_URL'),
            'monitor_gold_gm'       => env('DISCORD_MONITOR_GOLD_GM_WEBHOOK_URL'),
            'monitor_money_trading' => env('DISCORD_MONITOR_MONEY_TRADING_WEBHOOK_URL'),
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
    ],
    'jx_monitor' => [
        'multi_login_excluded_accounts' => [],
        'multiple_pc_pm_excluded_accounts' => [],
    ],
    'vip_system' => [
        'start_date' => new DateTime('@' . strtotime('2020-03-26')),
        'bonus_accs' => [],
        'levels'     => [],
    ],
    'features' => [
        'post_grouping_enabled' => false
    ],
    'widgets' => [
        'ranking' => [
            'enabled'       => true,
            'service_class' => \T2G\Common\Services\Kibana\JXRankingService::class,
            'servers'       => [],
        ],
    ],
];
