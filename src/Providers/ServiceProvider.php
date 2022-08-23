<?php

namespace Neko\LaravelStapler\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Neko\LaravelStapler\Commands\RefreshCommand;
use Neko\LaravelStapler\Services\ImageRefreshService;

abstract class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap up the stapler package:
     * - Boot stapler.
     * - Set the config driver.
     * - Set public_path config using laravel's public_path() method (if necessary).
     * - Set base_path config using laravel's base_path() method (if necessary).
     */
    abstract protected function bootstrapStapler();

    /**
     * Register the stapler fasten command with the container.
     */
    abstract protected function registerStaplerFastenCommand();

    /**
     * Register the service provider.
     */
    public function register()
    {
        // commands
        $this->registerStaplerFastenCommand();
        $this->registerStaplerRefreshCommand();

        // services
        $this->registerImageRefreshService();

        $this->commands('stapler.fasten');
        $this->commands('stapler.refresh');
    }

    /**
     * Register the stapler refresh command with the container.
     */
    protected function registerStaplerRefreshCommand()
    {
        $this->app->bind('stapler.refresh', fn($app) => new RefreshCommand($app['ImageRefreshService']));
    }

    /**
     * Register the image refresh service with the container.
     */
    protected function registerImageRefreshService()
    {
        $this->app->singleton('ImageRefreshService', fn($app) => new ImageRefreshService($app));
    }
}
