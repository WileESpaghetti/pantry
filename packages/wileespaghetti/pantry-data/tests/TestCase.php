<?php

namespace Tests;

use Pantry\Providers\PantryDataPackageServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            PantryDataPackageServiceProvider::class,
        ];
    }
}
