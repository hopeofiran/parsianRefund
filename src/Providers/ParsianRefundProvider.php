<?php

namespace HopeOfIran\ParsianRefund\Providers;

use HopeOfIran\ParsianRefund\ParsianRefund;
use Illuminate\Support\ServiceProvider;

class ParsianRefundProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            ParsianRefund::getDefaultConfigPath() => config_path('parsianRefund.php'),
        ], 'config');
    }
    /**
     * @return void
     */
    public function register()
    {
        /**
         * Bind to service container.
         */
        $this->app->bind('parsian-refund', function () {
            $config = config('parsianRefund') ?? [];
            return new ParsianRefund($config);
        });
    }
}
