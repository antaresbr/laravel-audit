<?php
namespace Antares\Audit\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFile('audit');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(ai_audit_path('lang'), 'audit');

        $this->loadRoutes();
    }

    protected function mergeConfigFile($name)
    {
        $targetFile = ai_audit_path("config/{$name}.php");

        if (is_file($targetFile) and !Config::has($name)) {
            $this->mergeConfigFrom($targetFile, $name);
        }
    }

    protected function loadRoutes()
    {
        $attributes = [
            'prefix' => config('audit.route.prefix.api'),
            'namespace' => 'Antares\Audit\Http\Controllers',
        ];
        Route::group($attributes, function () {
            $this->loadRoutesFrom(ai_audit_path('routes/api.php'));
        });
    }
}
