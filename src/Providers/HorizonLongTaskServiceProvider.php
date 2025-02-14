<?php

namespace MathieuAlbanese\HorizonLongTask\Providers;

use Illuminate\Support\ServiceProvider;

class HorizonLongTaskServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/horizon-longtask.php', 'horizon-longtask'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/horizon-longtask.php' => config_path('horizon-longtask.php'),
            ], 'config');
        }
    }
}
