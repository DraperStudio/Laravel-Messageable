<?php



declare(strict_types=1);



namespace BrianFaust\Tests\Messageable;

use GrahamCampbell\TestBench\AbstractPackageTestCase;

abstract class AbstractTestCase extends AbstractPackageTestCase
{
    /**
     * Get the service provider class.
     *
     * @param \Illuminate\Interfaces\Foundation\Application $app
     *
     * @return string
     */
    protected function getServiceProviderClass($app): string
    {
        return \BrianFaust\Messageable\ServiceProvider::class;
    }
}
