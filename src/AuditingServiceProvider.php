<?php

namespace wilianx7\Auditing;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use wilianx7\Auditing\Console\AuditDriverCommand;
use wilianx7\Auditing\Console\AuditResolverCommand;
use wilianx7\Auditing\Console\InstallCommand;
use wilianx7\Auditing\Contracts\Auditor;

class AuditingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishing();
        $this->mergeConfigFrom(__DIR__ . '/../config/audit.php', 'audit');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            AuditDriverCommand::class,
            AuditResolverCommand::class,
            InstallCommand::class,
        ]);

        $this->app->singleton(Auditor::class, function ($app) {
            return new \wilianx7\Auditing\Auditor($app);
        });

        $this->app->register(AuditingEventServiceProvider::class);
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            // Lumen lacks a config_path() helper, so we use base_path()
            $this->publishes([
                __DIR__ . '/../config/audit.php' => base_path('config/audit.php'),
            ], 'config');

            if (!class_exists('CreateAuditsTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/audits.stub' => database_path(
                        sprintf('migrations/%s_create_audits_table.php', date('Y_m_d_His'))
                    ),
                ], 'migrations');
            }
        }
    }
}
