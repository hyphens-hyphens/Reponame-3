## Docs
- Setup mailbox config for MoMo alert with Webklex\IMAP
- Register commands in cronjobs
- Configure middleware
```php
// app/Http/Kernel.php
't2g' => \T2G\Common\Middleware\T2GMiddleware::class,
```

- Configure logging channel

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
after correct configs in `config/t2g_common.php` run this commands
```
php artisan migrate
```

# Sync user to game server
## sync single user
```
php artisan t2g_common:sync:user --username=username01
```
## sync users by date after [ex: meaning from '2021-10-14' to present]
```
php artisan t2g_common:sync:user --date=2021-10-13
```

## TODO
