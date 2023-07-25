<?php

namespace DrH\Mpesa;

use DrH\Mpesa\Commands\C2bRegisterUrls;
use DrH\Mpesa\Commands\StkQuery;
use DrH\Mpesa\Commands\TransactionStatus;
use DrH\Mpesa\Events\C2bConfirmationEvent;
use DrH\Mpesa\Events\StkPushPaymentFailedEvent;
use DrH\Mpesa\Events\StkPushPaymentSuccessEvent;
use DrH\Mpesa\Http\Middlewares\MpesaCors;
use DrH\Mpesa\Library\B2BPayment;
use DrH\Mpesa\Library\BulkSender;
use DrH\Mpesa\Library\C2bRegister;
use DrH\Mpesa\Library\Core;
use DrH\Mpesa\Library\IdCheck;
use DrH\Mpesa\Library\StkPush;
use DrH\Mpesa\Listeners\C2bPaymentConfirmation;
use DrH\Mpesa\Listeners\StkPaymentFailed;
use DrH\Mpesa\Listeners\StkPaymentSuccessful;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class MpesaServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     * @throws Exceptions\ClientException
     */
    public function register(): void
    {
        $core = new Core(new Client());
        $this->app->bind(Core::class, function () use ($core) {
            return $core;
        });
        $this->commands(
            [
                C2bRegisterUrls::class,
                StkQuery::class,
                TransactionStatus::class,
            ]
        );

        $this->registerFacades();
        $this->registerEvents();
        $this->mergeConfigFrom(__DIR__ . '/../../config/drh.mpesa.php', 'drh.mpesa');
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->publishes([__DIR__ . '/../../config/drh.mpesa.php' => config_path('drh.mpesa.php'),]);

        $this->app['router']->aliasMiddleware('pesa.cors', MpesaCors::class);

        $this->requireHelperScripts();
    }

    /**
     * Register facade accessors
     */
    private function registerFacades(): void
    {
        $this->app->bind(
            'mpesa_stk',
            function () {
                return $this->app->make(StkPush::class);
            }
        );
        $this->app->bind(
            'mpesa_registrar',
            function () {
                return $this->app->make(C2bRegister::class);
            }
        );
        $this->app->bind(
            'mpesa_identity',
            function () {
                return $this->app->make(IdCheck::class);
            }
        );
        $this->app->bind(
            'mpesa_bulk',
            function () {
                return $this->app->make(BulkSender::class);
            }
        );
        $this->app->bind(
            'mpesa_b2b',
            function () {
                return $this->app->make(B2BPayment::class);
            }
        );
    }

    /**
     * Register events
     */
    private function registerEvents(): void
    {
        Event::listen(StkPushPaymentSuccessEvent::class, StkPaymentSuccessful::class);
        Event::listen(StkPushPaymentFailedEvent::class, StkPaymentFailed::class);

        Event::listen(C2bConfirmationEvent::class, C2bPaymentConfirmation::class);
    }

    private function requireHelperScripts(): void
    {
        $files = glob(__DIR__ . '/../Support/*.php');
        foreach ($files as $file) {
            include_once $file;
        }
    }
}
