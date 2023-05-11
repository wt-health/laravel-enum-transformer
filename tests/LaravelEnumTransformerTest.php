<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\Tests;

use BenSampo\Enum\Enum;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Webtools\LaravelEnumTransformer\LaravelEnumTransformer;

class LaravelEnumTransformerTest extends TestCase
{
    use MatchesSnapshots;

    public function test_it_will_check_if_an_enum_can_be_transformed()
    {
        $enum = new class(10) extends Enum {
            public const ADMIN = 10;
            public const USER = 20;
        };

        $noEnum = new class() {
        };

        $config = TypeScriptTransformerConfig::create();

        $transformer = new LaravelEnumTransformer($config);

        $this->assertNotNull($transformer->transform(new ReflectionClass($enum)));
        $this->assertNull($transformer->transform(new ReflectionClass($noEnum)));
    }

    public function test_it_can_transform_an_enum_to_type()
    {
        $enum = new class('foobar') extends Enum {
            public const ADMIN = 10;
            public const USER = 20;
            public const STRING_USER = 'foobar';
        };

        $config = TypeScriptTransformerConfig::create();

        $transformer = new LaravelEnumTransformer($config);

        $type = $transformer->transform(new ReflectionClass($enum), 'Enum');

        $this->assertMatchesSnapshot($type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
    }

    public function test_it_can_transform_an_enum_to_js_enum()
    {
        $enum = new class('foobar') extends Enum {
            public const ADMIN = 10;
            public const USER = 20;
            public const STRING_USER = 'foobar';
        };

        $config = TypeScriptTransformerConfig::create();

        $transformer = new LaravelEnumTransformer($config->transformToNativeEnums(true));

        $type = $transformer->transform(new ReflectionClass($enum), 'Enum');

        $this->assertMatchesSnapshot($type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
    }
}
