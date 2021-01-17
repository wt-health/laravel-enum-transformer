<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\Tests;

use BenSampo\Enum\Enum;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\Snapshots\MatchesSnapshots;
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

        $transformer = new LaravelEnumTransformer();

        $this->assertTrue($transformer->canTransform(new ReflectionClass($enum)));
        $this->assertFalse($transformer->canTransform(new ReflectionClass($noEnum)));
    }

    public function test_it_can_transform_an_enum()
    {
        $enum = new class('foobar') extends Enum {
            public const ADMIN = 10;
            public const USER = 20;
            public const STRING_USER = 'foobar';
        };

        $transformer = new LaravelEnumTransformer();

        $type = $transformer->transform(new ReflectionClass($enum), 'Enum');

        $this->assertMatchesSnapshot($type->transformed);
        $this->assertTrue($type->missingSymbols->isEmpty());
    }
}
