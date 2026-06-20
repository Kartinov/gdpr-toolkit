<?php

namespace Kartinov\GdprToolkit\Tests;

use Kartinov\GdprToolkit\GdprToolkitServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            GdprToolkitServiceProvider::class,
        ];
    }
}
