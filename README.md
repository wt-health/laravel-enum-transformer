Laravel Enum Transformer
================================

[![CI Action](https://github.com/wt-health/laravel-enum-transformer/workflows/CI/badge.svg)](https://github.com/wt-health/laravel-enum-transformer/actions?query=workflow%3ACI)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wt-health/laravel-enum-transformer/badges/quality-score.png?b=master&s=05927bab19f5d8f124e2860c16c9e9e129bfe6df)](https://scrutinizer-ci.com/g/wt-health/laravel-enum-transformer/?branch=master)

Adds transformation support for [bensampo/laravel-enum](https://github.com/BenSampo/laravel-enum) based enums to
[spatie/typescript-transformer](https://github.com/spatie/typescript-transformer) v3 and
[spatie/laravel-typescript-transformer](https://github.com/spatie/laravel-typescript-transformer) v3.

| This package | spatie/typescript-transformer | PHP  | Laravel |
|--------------|-------------------------------|------|---------|
| 2.x          | ^3.0                          | ^8.3 | 12, 13  |
| 1.x          | ^2.1.3                        | ^8.0 | 8 - 13  |

Version 2 only supports typescript-transformer v3. v3 changed the `Transformer` interface, keeping the same name but
giving it different method signatures, so one class cannot work with both versions. If you are still on
typescript-transformer v2, stay on `^1.1`.

Installation
------------

```bash
composer require wthealth/laravel-enum-transformer:^2.0 spatie/laravel-typescript-transformer:^3.3
```

Configuration
-------------

typescript-transformer v3 no longer uses `config/typescript-transformer.php`. You configure it in a service provider
that extends `TypeScriptTransformerApplicationServiceProvider`. Running `php artisan typescript:install` publishes
`app/Providers/TypeScriptTransformerServiceProvider.php` and registers it in `bootstrap/providers.php`. You can also
write the file yourself. Register `LaravelEnumTransformer` in it:

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerApplicationServiceProvider as BaseTypeScriptTransformerServiceProvider;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;
use Webtools\LaravelEnumTransformer\LaravelEnumTransformer;

class TypeScriptTransformerServiceProvider extends BaseTypeScriptTransformerServiceProvider
{
    protected function configure(TypeScriptTransformerConfigFactory $config): void
    {
        $config
            // `useUnionEnums: false` replaces the old `'transform_to_native_enums' => true`
            ->transformer(new LaravelEnumTransformer(useUnionEnums: false))
            ->transformDirectories(app_path())
            // replaces `'output_file' => resource_path('app/Types/generated.ts')` with the v2 `ModuleWriter`
            ->outputDirectory(resource_path('app/Types'))
            ->writer(new FlatModuleWriter('generated.ts'))
            // stops typescript-transformer-manifest.json being written next to generated.ts (needs typescript-transformer ^3.3)
            ->withoutManifest();
    }
}
```

If you wrote the provider yourself, register it in `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\TypeScriptTransformerServiceProvider::class,
];
```

Then generate the types as before:

```bash
php artisan typescript:transform
```

### Migrating from 1.x / typescript-transformer v2

| v2 (`config/typescript-transformer.php`)                       | v3 (`TypeScriptTransformerServiceProvider::configure()`)                     |
|----------------------------------------------------------------|------------------------------------------------------------------------------|
| `'auto_discover_types' => [app_path()]`                        | `->transformDirectories(app_path())`                                         |
| `'collectors' => [DefaultCollector::class]`                    | removed: each transformer decides what it transforms (see below)             |
| `'transformers' => [LaravelEnumTransformer::class]`            | `->transformer(new LaravelEnumTransformer(...))`                             |
| `'transform_to_native_enums' => true`                          | `new LaravelEnumTransformer(useUnionEnums: false)`                           |
| `'transform_to_native_enums' => false` (default)               | `new LaravelEnumTransformer()` (or `LaravelEnumTransformer::class`)          |
| `'output_file' => resource_path('app/Types/generated.ts')`     | `->outputDirectory(resource_path('app/Types'))` + writer path `generated.ts` |
| `'writer' => ModuleWriter::class`                              | `->writer(new FlatModuleWriter('generated.ts'))` (one file, ES module)       |
| `'writer' => TypeDefinitionWriter::class`                      | `->writer(new GlobalNamespaceWriter('generated.d.ts'))`                      |
| `'default_type_replacements' => [DateTime::class => 'string']` | `->replaceType(DateTime::class, 'string')`                                   |
| `'formatter' => PrettierFormatter::class`                      | `->formatter(PrettierFormatter::class)`                                      |

After that, delete `config/typescript-transformer.php`.

Things to be aware of:

* **Use `FlatModuleWriter`, not `ModuleWriter`.** In v3, `ModuleWriter` no longer writes a single file. It writes one
  `index.ts` per PHP namespace (`types/App/Enums/index.ts`).
* **Every concrete `BenSampo\Enum\Enum` subclass** in the configured directories is transformed. This matches what
  Spatie's v3 `EnumTransformer` does for native enums. The `@typescript` docblock annotation is no longer needed and is
  ignored. You can still use the `#[TypeScript(name: ..., location: ...)]` attribute to rename an enum. Abstract enums
  are skipped, and `#[Spatie\TypeScriptTransformer\Attributes\Hidden]` excludes an enum.
* The command is still `php artisan typescript:transform`. It also has a new `--watch` mode, which this transformer supports.
  In watch mode PHP can't re-load an edited class, so enum constants are read from the source file: overrides of `getKeys()`, `getValue()` or `getConstants()` aren't applied to watch updates, only to full `typescript:transform` runs.
* Enums with values TypeScript can't represent are skipped in both union and native mode, instead of generating invalid TypeScript or aborting the transform: anything other than strings and integers (e.g. `bool`, `null`, arrays), strings that aren't valid UTF-8, and integers outside JavaScript's safe range (±2^53 - 1).
* The output directory must exist before the command runs.

Usage
-----

Any enum extending `BenSampo\Enum\Enum` is transformed:

```php
final class UserType extends Enum
{
    const Administrator = 0;
    const Moderator = 1;
    const Subscriber = 'subscriber';
}
```

`new LaravelEnumTransformer(useUnionEnums: false)` turns it into a native TypeScript enum, with the same formatting as
1.x (two space indentation, JSON encoded values):

```typescript
export enum UserType {
  Administrator = 0,
  Moderator = 1,
  Subscriber = "subscriber",
}
```

`new LaravelEnumTransformer()` (the default) turns it into a union of its values:

```typescript
export type UserType = 0 | 1 | "subscriber";
```

> In 1.x the non-native mode produced an object-like type (`export type UserType = { Administrator: 0, ... }`).
> 2.x writes a union of the values instead, which is what Spatie's `EnumTransformer` does for native PHP enums.
> An enum with values that are not strings or integers can't be written as a union, so it is skipped in that mode.

### Using Spatie's `EnumTransformer` instead

If you want the same formatting that Spatie's transformer uses for native enums (four space indentation, single
quotes), pass `BenSampoEnumProvider` to it:

```php
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Webtools\LaravelEnumTransformer\EnumProviders\BenSampoEnumProvider;

$config->transformer(new EnumTransformer(useUnionEnums: false, enumProvider: new BenSampoEnumProvider()));
```

See the [typescript-transformer documentation](https://spatie.be/docs/typescript-transformer/v3/introduction) for more.

Development
-----------

```bash
composer test      # PHPUnit
composer lint      # Pint (composer format to fix)
composer analyse   # PHPStan / Larastan
```

License
-------
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
