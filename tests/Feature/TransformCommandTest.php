<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\Tests\Feature;

use Composer\InstalledVersions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;
use Webtools\LaravelEnumTransformer\LaravelEnumTransformer;
use Webtools\LaravelEnumTransformer\Tests\Support\TypeScriptTransformerTestServiceProvider;
use Webtools\LaravelEnumTransformer\Tests\TestCase;

class TransformCommandTest extends TestCase
{
    private string $outputDirectory;

    protected function setUp(): void
    {
        $this->outputDirectory = sys_get_temp_dir().'/laravel-enum-transformer-'.bin2hex(random_bytes(6));
        mkdir($this->outputDirectory, recursive: true);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->outputDirectory);
        TypeScriptTransformerTestServiceProvider::$configure = null;

        parent::tearDown();
    }

    #[Test]
    public function it_generates_the_same_file_as_the_v2_native_enum_setup(): void
    {
        $this->configure(fn (TypeScriptTransformerConfigFactory $config) => $config
            ->transformer(new LaravelEnumTransformer(useUnionEnums: false))
            ->transformDirectories(__DIR__.'/../Fixtures/Boilerplate')
            ->outputDirectory($this->outputDirectory)
            ->writer(new FlatModuleWriter('generated.ts')));

        $this->assertSame(0, Artisan::call('typescript:transform'));

        // Byte for byte what laravel-react-boilerplate-v2 has in resources/app/Types/generated.ts
        // with spatie/laravel-typescript-transformer 2 and `transform_to_native_enums => true`.
        $this->assertSame(
            "export enum Roles {\n  MASTER = \"Master\",\n  ADMIN = \"Admin\",\n  USER = \"User\",\n}\n",
            file_get_contents($this->outputDirectory.'/generated.ts'),
        );
        $this->assertSame(
            self::supportsWithoutManifest()
                ? ['generated.ts']
                : ['generated.ts', 'typescript-transformer-manifest.json'],
            array_values(array_diff(scandir($this->outputDirectory) ?: [], ['.', '..'])),
        );
    }

    #[Test]
    public function it_generates_union_types_for_every_concrete_enum_in_the_directories(): void
    {
        $this->configure(fn (TypeScriptTransformerConfigFactory $config) => $config
            ->transformer(LaravelEnumTransformer::class)
            ->transformDirectories(__DIR__.'/../Fixtures')
            ->outputDirectory($this->outputDirectory)
            ->writer(new FlatModuleWriter('generated.ts')));

        $this->assertSame(0, Artisan::call('typescript:transform'));

        $this->assertSame(<<<'TS'
            export type MixedValues = 10 | 20 | "foobar";
            export type OverriddenHooks = "VISIBLE";
            export type Priority = 0 | 10 | 20;
            export type Roles = "Master" | "Admin" | "User";

            TS, file_get_contents($this->outputDirectory.'/generated.ts'));
    }

    private static function supportsWithoutManifest(): bool
    {
        return version_compare(InstalledVersions::getVersion('spatie/typescript-transformer') ?? '0', '3.3.0', '>=');
    }

    /**
     * @param  \Closure(TypeScriptTransformerConfigFactory): mixed  $configure
     */
    private function configure(\Closure $configure): void
    {
        TypeScriptTransformerTestServiceProvider::$configure = function (TypeScriptTransformerConfigFactory $config) use ($configure): void {
            $configure($config);

            // withoutManifest() only exists from spatie/typescript-transformer 3.3.
            if (self::supportsWithoutManifest()) {
                $config->withoutManifest();
            }
        };
    }
}
