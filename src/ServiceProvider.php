<?php

namespace T2G\Common;

use Illuminate\Log\LogManager;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use T2G\Common\Action\AcceptPaymentAction;
use T2G\Common\Action\RejectPaymentAction;
use T2G\Common\Console\Commands\MoMoTransactionNotifierCommand;
use T2G\Common\Console\Commands\MysqlBackupCommand;
use T2G\Common\Console\Commands\UpdatePaymentStatusCodeCommand;
use T2G\Common\Contract\CardPaymentInterface;
use T2G\Common\Event\PostCreatingEvent;
use T2G\Common\Listeners\PostCreatingListener;
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
        $this->registerHelpers();
        $this->publishes([
            __DIR__.'/../resources/config/t2g_common.php' => config_path('t2g_common.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                MoMoTransactionNotifierCommand::class,
                MysqlBackupCommand::class,
                UpdatePaymentStatusCodeCommand::class
            ]);
        }
        t2g_model('user')->observe(UserObserver::class);
        t2g_model('payment')->observe(PaymentObserver::class);
        voyager()->addAction(AcceptPaymentAction::class);
        voyager()->addAction(RejectPaymentAction::class);

        /** @var \Illuminate\Events\Dispatcher $dispatcher */
        $dispatcher = app(\Illuminate\Events\Dispatcher::class);
        $dispatcher->listen(PostCreatingEvent::class, PostCreatingListener::class);
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
                env('RECARD_MERCHANT_ID'),
                env('RECARD_SECRET_KEY')
            );
            $service->setLogger($this->getLogger());

            return $service;
        });

        $this->app->singleton(NapTheNhanhPayment::class, function ($app) {
            $service = new NapTheNhanhPayment(
                env('NAPTHENHANH_PARTNER_ID'),
                env('NAPTHENHANH_PARTNER_KEY')
            );
            $service->setLogger($this->getLogger());

            return $service;
        });

        $this->app->singleton(CardPaymentInterface::class, function($app) {
            /** @var \TCG\Voyager\Voyager $voyager */
            $voyager = app('voyager');
            $partnerSetting = $voyager->setting('site.card_payment_partner', env('CARD_PAYMENT_PARTNER'));
            if (CardPaymentInterface::PARTNER_NAPTHENHANH == $partnerSetting) {
                return app(NapTheNhanhPayment::class);
            } else {
                return app(RecardPayment::class);
            }
        });
    }

    /**
     * @return \Illuminate\Log\LogManager
     */
    private function getLogger()
    {
        /** @var LogManager $logger */
        $logger = app(LogManager::class);
        $logger->channel('card_payment');

        return $logger;
    }

    private function registerGameApiClient()
    {
        $this->app->singleton(JXApiClient::class, function ($app) {
            $baseUrl = env('GAME_API_BASE_URL', '');
            $apiKey = env('GAME_API_KEY', '');

            return new JXApiClient($baseUrl, $apiKey);
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
}
