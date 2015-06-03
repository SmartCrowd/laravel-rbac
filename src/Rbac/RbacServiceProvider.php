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
            __DIR__ . '/install/config/rbac.php' => config_path('rbac.php'),
            __DIR__ . '/install/Rbac' => app_path('Rbac'),
        ]);

        if (Config::has('rbac.itemsPath')) {
            require Config::get('rbac.itemsPath');
        }

        if (Config::has('rbac.actionsPath')) {
            require Config::get('rbac.actionsPath');
        }
    }
}