<?php
namespace Antares\Audit\Providers;

use Illuminate\Support\ServiceProvider;

class AuditConsoleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            \Antares\Audit\Console\CreateConfigCommand::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishResources();
        }
    }

    protected function publishResources()
    {
        $this->publishes([
            ai_audit_path('config/audit.php') => config_path('audit.php'),
        ], 'audit-config');

        $this->publishes([
            ai_audit_path('lang') => resource_path('lang/vendor/audit'),
        ], 'audit-lang');
    }
}
