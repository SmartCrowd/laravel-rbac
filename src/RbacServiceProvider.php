<?php
namespace SmartCrowd\Rbac;

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
        $this->app->bind('rbac', '\\SmartCrowd\\Rbac\\Rbac');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/rbac.php' => config_path('rbac.php'),
        ]);
    }
}