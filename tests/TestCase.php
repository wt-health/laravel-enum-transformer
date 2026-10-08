<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerServiceProvider;
use Webtools\LaravelEnumTransformer\Tests\Support\TypeScriptTransformerTestServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            TypeScriptTransformerServiceProvider::class,
            TypeScriptTransformerTestServiceProvider::class,
        ];
    }
}
