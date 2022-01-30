<?php

namespace DrH\Mpesa;

use DrH\Mpesa\Commands\StkStatus;
use DrH\Mpesa\Events\B2cPaymentFailedEvent;
use DrH\Mpesa\Events\B2cPaymentSuccessEvent;
use DrH\Mpesa\Events\C2bConfirmationEvent;
use DrH\Mpesa\Events\StkPushPaymentFailedEvent;
use DrH\Mpesa\Events\StkPushPaymentSuccessEvent;
use DrH\Mpesa\Http\Middlewares\MpesaCors;
use DrH\Mpesa\Library\BulkSender;
use DrH\Mpesa\Library\Core;
use DrH\Mpesa\Library\IdCheck;
use DrH\Mpesa\Library\RegisterUrl;
use DrH\Mpesa\Library\StkPush;
use DrH\Mpesa\Listeners\B2cFailedListener;
use DrH\Mpesa\Listeners\B2cSuccessListener;
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
     * @throws Exceptions\MpesaException
     */
    public function register()
    {
        $core = new Core(new Client());
        $this->app->bind(Core::class, function () use ($core) {
            return $core;
        });
        $this->commands(
            [
                StkStatus::class,
            ]
        );

        $this->registerFacades();
        $this->registerEvents();
        $this->mergeConfigFrom(__DIR__ . '/../../config/drh.mpesa.php', 'drh.mpesa');
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->publishes([__DIR__ . '/../../config/drh.mpesa.php' => config_path('drh.mpesa.php'),]);

        $this->app['router']->aliasMiddleware('pesa.cors', MpesaCors::class);

        $this->requireHelperScripts();
    }

    /**
     * Register facade accessors
     */
    private function registerFacades()
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
                return $this->app->make(RegisterUrl::class);
            }
        );
        $this->app->bind(
            'mpesa_identity',
            function () {
                return $this->app->make(IdCheck::class);
            }
        );
        $this->app->bind(
            'mpesa_b2c',
            function () {
                return $this->app->make(BulkSender::class);
            }
        );
    }

    /**
     * Register events
     */
    private function registerEvents()
    {
        Event::listen(StkPushPaymentSuccessEvent::class, StkPaymentSuccessful::class);
        Event::listen(StkPushPaymentFailedEvent::class, StkPaymentFailed::class);
        Event::listen(C2bConfirmationEvent::class, C2bPaymentConfirmation::class);
        Event::listen(B2cPaymentSuccessEvent::class, B2cSuccessListener::class);
        Event::listen(B2cPaymentFailedEvent::class, B2cFailedListener::class);
    }

    private function requireHelperScripts()
    {
        $files = glob(__DIR__ . '/../Support/*.php');
        foreach ($files as $file) {
            include_once $file;
        }
    }
}
