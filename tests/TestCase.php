<?php
namespace Antares\Audit\Tests;

use Antares\Audit\Providers\AuditServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            AuditServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        $this->artisan('migrate', [
            '--database' => config('database.default'),
            '--path' => realpath(__DIR__ . '/Database/migrations'),
            '--realpath' => true,
        ])->run();
    }
}
