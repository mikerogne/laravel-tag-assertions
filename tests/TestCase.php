<?php

namespace Rogne\LaravelTagAssertions\Tests;

use Rogne\LaravelTagAssertions\ServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }
}
