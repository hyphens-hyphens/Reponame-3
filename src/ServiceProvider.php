<?php

namespace T2G\Common;

use Illuminate\Log\LogManager;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use T2G\Common\Action\AcceptPaymentAction;
use T2G\Common\Action\RejectPaymentAction;
use T2G\Common\Console\Commands\ExportMultipleLoginCommand;
use T2G\Common\Console\Commands\ImportUserLastLoginCommand;
use T2G\Common\Console\Commands\MoMoTransactionNotifierCommand;
use T2G\Common\Console\Commands\MonitorJXGMGoldCommand;
use T2G\Common\Console\Commands\MonitorJXGoldCommand;
use T2G\Common\Console\Commands\MonitorJXGoldTradingCommand;
use T2G\Common\Console\Commands\MonitorJXMoneyTradingCommand;
use T2G\Common\Console\Commands\MonitorKimYenKeoXeCommand;
use T2G\Common\Console\Commands\MonitorMultipleLoginCommand;
use T2G\Common\Console\Commands\MonitorMultiplePCCommand;
use T2G\Common\Console\Commands\MysqlBackupCommand;
use T2G\Common\Console\Commands\SyncUserCommand;
use T2G\Common\Console\Commands\UpdateCCUCommand;
use T2G\Common\Console\Commands\UpdatePaymentProfitCommand;
use T2G\Common\Console\Commands\UpdatePaymentStatusCodeCommand;
use T2G\Common\Console\Commands\UpdateUserLastLoginCommand;
use T2G\Common\Contract\CardPaymentInterface;
use T2G\Common\Event\PostModelEvent;
use T2G\Common\Listeners\PostCreatingListener;
use T2G\Common\Listeners\PostSavingListener;
use T2G\Common\Observers\PaymentObserver;
use T2G\Common\Observers\UserObserver;
use T2G\Common\Services\JXApiClient;
use T2G\Common\Services\NapTheNhanhPayment;
use T2G\Common\Services\RecardPayment;

/**
 * Class ServiceProvider
 *
 * @package \T2G\Common
 */
class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 't2g_common');

        $this->publishes([
            __DIR__.'/../resources/config/t2g_common.php' => config_path('t2g_common.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/t2g_common')
        ], 'view');

        $this->loadMigrationsFrom(__DIR__.'/../resources/migrations');

        $this->loadRoutesFrom(__DIR__.'/../resources/routes/route.php');

        $this->registerHelpers();
        $this->registerCommands();

        // register observers
        t2g_model('user')->observe(UserObserver::class);
        t2g_model('payment')->observe(PaymentObserver::class);

        // Add Voyager actions for Payment
        voyager()->addAction(AcceptPaymentAction::class);
        voyager()->addAction(RejectPaymentAction::class);

        /** @var \Illuminate\Events\Dispatcher $dispatcher */
        $dispatcher = app(\Illuminate\Events\Dispatcher::class);
        $dispatcher->listen(PostModelEvent::class, PostCreatingListener::class);
        $dispatcher->listen(PostModelEvent::class, PostSavingListener::class);
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../resources/config/t2g_common.php', 't2g_common');
        $this->registerCardPaymentService();
        $this->registerGameApiClient();
    }

    private function registerCardPaymentService()
    {
        $this->app->singleton(RecardPayment::class, function ($app) {
            $service = new RecardPayment(
                config('t2g_common.payment.recard.merchant_id'),
                config('t2g_common.payment.recard.secret_key')
            );
            $service->setLogger($this->getCardPaymentLogger());

            return $service;
        });

        $this->app->singleton(NapTheNhanhPayment::class, function ($app) {
            $service = new NapTheNhanhPayment(
                config('t2g_common.payment.napthenhanh.partner_id'),
                config('t2g_common.payment.napthenhanh.partner_key')
            );
            $service->setLogger($this->getCardPaymentLogger());

            return $service;
        });

        $this->app->singleton(CardPaymentInterface::class, function($app) {
            /** @var \TCG\Voyager\Voyager $voyager */
            $voyager = app('voyager');
            $partnerSetting = $voyager->setting('site.card_payment_partner', config('t2g_common.payment.card_payment_partner'));
            if (CardPaymentInterface::PARTNER_NAPTHENHANH == $partnerSetting) {
                return app(NapTheNhanhPayment::class);
            } else {
                return app(RecardPayment::class);
            }
        });

        $this->app->singleton('game_api_log', function () {
            return app('log')->channel('game_api');
        });
    }

    /**
     * @return \Illuminate\Log\LogManager
     */
    private function getCardPaymentLogger()
    {
        /** @var LogManager $logger */
        $logger = app(LogManager::class);
        $logger = $logger->channel('card_payment');

        return $logger;
    }

    private function registerGameApiClient()
    {
        $this->app->singleton(JXApiClient::class, function ($app) {
            // support multiple Game API base URLs
            $baseUrl = config('t2g_common.game_api.base_url');
            $baseUrls = explode(',', $baseUrl);
            $apiKey = config('t2g_common.game_api.api_key');

            return new JXApiClient($baseUrls, $apiKey);
        });
    }

    /**
     * Register helpers file
     */
    public function registerHelpers()
    {
        // Load the helpers in app/Http/helpers.php
        if (file_exists($file = __DIR__ . "/helpers.php"))
        {
            require_once $file;
        }
    }

    /**
     * Register console commands
     */
    private function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MoMoTransactionNotifierCommand::class,
                MysqlBackupCommand::class,
                UpdatePaymentStatusCodeCommand::class,
                UpdatePaymentProfitCommand::class,
                UpdateCCUCommand::class,
                UpdateUserLastLoginCommand::class,
                ImportUserLastLoginCommand::class,
                SyncUserCommand::class,
                MonitorJXGoldCommand::class,
                MonitorMultiplePCCommand::class,
                MonitorJXGoldTradingCommand::class,
                MonitorJXMoneyTradingCommand::class,
                MonitorJXGMGoldCommand::class,
                MonitorMultipleLoginCommand::class,
                MonitorKimYenKeoXeCommand::class,
                ExportMultipleLoginCommand::class
            ]);
        }
    }
}
