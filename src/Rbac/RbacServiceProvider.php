<?php
namespace SmartCrowd\Rbac;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/install/config/rbac.php' => config_path('rbac.php'),
            __DIR__ . '/install/Rbac' => app_path('Rbac'),
        ]);

        if (Config::has('rbac.itemsPath') && file_exists(Config::get('rbac.itemsPath'))) {
            require Config::get('rbac.itemsPath');
        }

        if (Config::has('rbac.actionsPath') && file_exists(Config::get('rbac.itemsPath'))) {
            require Config::get('rbac.actionsPath');
        }

        Blade::directive('allowed', function($expression) {

            if (Str::startsWith($expression, '(')) {

                $expression = substr($expression, 1, -1);
                return "<?php if (app('rbac')->checkAccess(\\Auth::user(), {$expression})): ?>";

            }

            return '';
        });

        Blade::directive('endallowed', function($expression) {
            return "<?php endif; ?>";
        });
    }
}

