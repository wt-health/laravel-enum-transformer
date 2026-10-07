<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Spatie\TypeScriptTransformer\Actions\LoadPhpClassNodeAction;
use Spatie\TypeScriptTransformer\Data\TransformationContext;
use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Webtools\LaravelEnumTransformer\EnumProviders\BenSampoEnumProvider;
use Webtools\LaravelEnumTransformer\LaravelEnumTransformer;
use Webtools\LaravelEnumTransformer\Tests\Fixtures\Boilerplate\Enums\Roles;
use Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums\AbstractEnum;
use Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums\MalformedUtf8;
use Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums\MixedValues;
use Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums\NativeStatus;
use Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums\NotAnEnum;
use Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums\OverriddenHooks;
use Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums\Priority;
use Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums\UnsafeInteger;
use Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums\UnsupportedValues;

class LaravelEnumTransformerTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string, string}>
     */
    public static function nativeEnumProvider(): iterable
    {
        yield 'string values' => [Roles::class, <<<'TS'
            export enum Roles {
              MASTER = "Master",
              ADMIN = "Admin",
              USER = "User",
            }
            TS];

        yield 'int values' => [Priority::class, <<<'TS'
            export enum Priority {
              LOW = 0,
              MEDIUM = 10,
              HIGH = 20,
            }
            TS];

        yield 'mixed values' => [MixedValues::class, <<<'TS'
            export enum MixedValues {
              ADMIN = 10,
              USER = 20,
              STRING_USER = "foobar",
            }
            TS];
    }

    /**
     * @return iterable<string, array{class-string, string}>
     */
    public static function unionProvider(): iterable
    {
        yield 'string values' => [Roles::class, 'export type Roles = "Master" | "Admin" | "User";'];

        yield 'int values' => [Priority::class, 'export type Priority = 0 | 10 | 20;'];

        yield 'mixed values' => [MixedValues::class, 'export type MixedValues = 10 | 20 | "foobar";'];
    }

    /**
     * @param  class-string  $class
     */
    #[Test]
    #[DataProvider('nativeEnumProvider')]
    public function it_transforms_an_enum_to_a_native_typescript_enum(string $class, string $expected): void
    {
        $transformer = new LaravelEnumTransformer(useUnionEnums: false);

        $this->assertSame($expected, $this->write($transformer, PhpClassNode::fromClassString($class)));
    }

    /**
     * @param  class-string  $class
     */
    #[Test]
    #[DataProvider('unionProvider')]
    public function it_transforms_an_enum_to_a_union_type_by_default(string $class, string $expected): void
    {
        $transformer = new LaravelEnumTransformer;

        $this->assertSame($expected, $this->write($transformer, PhpClassNode::fromClassString($class)));
    }

    /**
     * @param  class-string  $class
     */
    #[Test]
    #[DataProvider('nativeEnumProvider')]
    public function it_reads_enums_reflected_statically_in_watch_mode(string $class, string $expected): void
    {
        $file = (new \ReflectionClass($class))->getFileName();
        $this->assertIsString($file);

        $node = (new LoadPhpClassNodeAction)->execute($file);
        $this->assertNotNull($node);
        $this->assertNotInstanceOf(\ReflectionClass::class, $node->reflection);

        $this->assertSame($expected, $this->write(new LaravelEnumTransformer(useUnionEnums: false), $node));
    }

    /**
     * @return iterable<string, array{class-string}>
     */
    public static function untransformableProvider(): iterable
    {
        yield 'plain class' => [NotAnEnum::class];
        yield 'abstract bensampo enum' => [AbstractEnum::class];
        yield 'native php enum' => [NativeStatus::class];
        yield 'values typescript cannot represent' => [UnsupportedValues::class];
        yield 'integer outside javascript safe range' => [UnsafeInteger::class];
        yield 'malformed utf-8 string' => [MalformedUtf8::class];
    }

    /**
     * @param  class-string  $class
     */
    #[Test]
    #[DataProvider('untransformableProvider')]
    public function it_only_transforms_concrete_bensampo_enums(string $class): void
    {
        foreach ([true, false] as $useUnionEnums) {
            $transformed = (new LaravelEnumTransformer($useUnionEnums))->transform(
                $node = PhpClassNode::fromClassString($class),
                TransformationContext::createFromPhpClass($node),
            );

            $this->assertInstanceOf(Untransformable::class, $transformed);
        }
    }

    #[Test]
    public function the_provider_skips_unsupported_values_with_spaties_enum_transformer_too(): void
    {
        foreach ([UnsupportedValues::class, UnsafeInteger::class, MalformedUtf8::class] as $class) {
            foreach ([true, false] as $useUnionEnums) {
                $transformer = new EnumTransformer($useUnionEnums, new BenSampoEnumProvider);

                $transformed = $transformer->transform(
                    $node = PhpClassNode::fromClassString($class),
                    TransformationContext::createFromPhpClass($node),
                );

                $this->assertInstanceOf(Untransformable::class, $transformed, $class);
            }
        }
    }

    #[Test]
    public function it_reads_enums_through_the_get_keys_and_get_value_hooks_like_1x(): void
    {
        $this->assertSame(<<<'TS'
            export enum OverriddenHooks {
              VISIBLE = "VISIBLE",
            }
            TS, $this->write(new LaravelEnumTransformer(useUnionEnums: false), PhpClassNode::fromClassString(OverriddenHooks::class)));
    }

    #[Test]
    public function the_enum_provider_can_be_used_with_spaties_enum_transformer(): void
    {
        $transformer = new EnumTransformer(useUnionEnums: false, enumProvider: new BenSampoEnumProvider);

        $this->assertSame(<<<'TS'
            export enum Roles {
                MASTER = 'Master',
                ADMIN = 'Admin',
                USER = 'User',
            }
            TS, $this->write($transformer, PhpClassNode::fromClassString(Roles::class)));
    }

    private function write(EnumTransformer $transformer, PhpClassNode $node): string
    {
        $transformed = $transformer->transform($node, TransformationContext::createFromPhpClass($node));

        $this->assertInstanceOf(Transformed::class, $transformed);
        $this->assertSame([], $transformed->getMissingReferences());

        return $transformed->write(new WritingContext([]));
    }
}
