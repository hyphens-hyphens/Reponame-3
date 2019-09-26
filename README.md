## Docs
- huong dan setup mailbox config for MoMo alert with Webklex\IMAP
- register commands in cronjobs
- config middleware
```php
// app/Http/Kernel.php
't2g' => \T2G\Common\Middleware\T2GMiddleware::class,
```

- config logging channel

```php
// ./config/logging.php
...
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'discord'],
        ],
        // logging configurations for T2G\Common
        'game_api' => [
            'driver' => 'stack',
            'channels' => ['discord', 'game_api_request'],
        ],

        'discord' => [
            'driver' => 'custom',
            'url' => env('LOG_DISCORD_WEBHOOK_URL', ''),
            'via' => \T2G\Common\Logging\DiscordMonologFactory::class,
            'level' => 'error',
        ],

        'game_api_request' => [
            'driver' => 'single',
            'path' => storage_path('logs/game_api.log'),
            'level' => 'debug',
        ],
        'card_payment' => [
            'driver' => 'single',
            'path' => storage_path('logs/card_payment.log'),
            'level' => 'debug',
        ],

        'card_payment_mocked' => [
            'driver' => 'single',
            'path' => storage_path('logs/card_payment_mocked.log'),
            'level' => 'debug',
        ],
        // end logging configurations for T2G\Common

       .....
    ],

```

## Publish config and view files
```
php artisan vendor:publish --provider="T2G\Common\ServiceProvider" --tag="config"
```
after correct configs in `config/t2g_common.php` run these commands
```
php artisan migrate
php artisan t2g_common:payment:update_profit
```

## Update database 
```mysql
UPDATE data_types SET model_name = REPLACE(model_name, 'App\\Models', 'T2G\\Common\\Models');

UPDATE data_types SET policy_name = REPLACE(model_name, 'App\\Models', 'T2G\\Common\\Models');
```

## TODO
