<?php
namespace SmartCrowd\Rbac;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class RbacServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('rbac', '\\SmartCrowd\\Rbac\\Manager');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/rbac.php' => config_path('rbac.php'),
        ]);

        $this->app->singleton('SmartCrowd\Rbac\Contracts\ItemsProviderInterface', Config::get('rbac.itemsProvider'));
    }
}