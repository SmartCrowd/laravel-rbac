<?php
namespace SmartCrowd\Rbac;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use SmartCrowd\Rbac\Facades\Rbac;

class RbacServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('SmartCrowd\Rbac\Manager');
        $this->registerItems();
        $this->registerActionsBindings();
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
            __DIR__ . '/install/Rbac'            => app_path('Rbac'),
        ]);

        $this->registerDirectives();
    }

    private function registerDirectives()
    {
        Blade::directive('allowed', function ($expression) {

            if (Str::startsWith($expression, '(')) {
                $expression = substr($expression, 1, -1);
            }

            return "<?php if (\\Rbac::checkAccess(\\Auth::user(), {$expression})): ?>";
        });

        if (Config::get('rbac.shortDirectives')) {

            foreach (Rbac::getRepository() as $name => $item) {

                $directiveName = $item->type == Item::TYPE_PERMISSION ? 'allowed' : 'is';
                $directiveName .= Str::studly(str_replace('.', ' ', $name));

                Blade::directive($directiveName, function ($expression) use ($name) {

                    $expression = trim($expression, '()');
                    if (!empty($expression)) {
                        $expression = ', ' . $expression;
                    }

                    return "<?php if (\\Rbac::checkAccess(\\Auth::user(), '{$name}'{$expression})): ?>";
                });
            }
        }

        Blade::directive('endallowed', function ($expression) {
            return "<?php endif; ?>";
        });
    }

    private function registerItems()
    {

        if (File::exists(Config::get('rbac.itemsPath'))) {
            File::requireOnce(Config::get('rbac.itemsPath'));
        }
    }

    private function registerActionsBindings()
    {
        if (File::exists(Config::get('rbac.actionsPath'))) {
            File::requireOnce(Config::get('rbac.actionsPath'));
        }
    }
}

