<?php

namespace BrianFaust\Messageable;

use BrianFaust\ServiceProvider\ServiceProvider;

class MessageableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishMigrations();
    }

    /**
     * Get the default package name.
     *
     * @return string
     */
    public function getPackageName()
    {
        return 'messageable';
    }
}
