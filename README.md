Laravel Enum Transformer
================================

[![CI Action](https://github.com/wt-health/laravel-enum-transformer/workflows/CI/badge.svg)](https://github.com/wt-health/laravel-enum-transformer/actions?query=workflow%3ACI)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wt-health/laravel-enum-transformer/badges/quality-score.png?b=master&s=05927bab19f5d8f124e2860c16c9e9e129bfe6df)](https://scrutinizer-ci.com/g/wt-health/laravel-enum-transformer/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/wt-health/laravel-enum-transformer/badges/coverage.png?b=master&s=bd88b309a345a8ad847c597e8c72c1916207e713)](https://scrutinizer-ci.com/g/wt-health/laravel-enum-transformer/?branch=master)

Adds transformation support for  [bensampo/laravel-enum](https://github.com/BenSampo/laravel-enum) based enums to the 
[spatie/laravel-typescript-transformer](https://github.com/spatie/laravel-typescript-transformer) package.

Installation & Configuration
--------------

```bash
 composer require wthealth/laravel-enum-transformer
```

Add the following `Transformer` to the configuration `config/type-script-transformer`

```
'transformers' => [
    Webtools\LaravelEnumTransformer\LaravelEnumTransformer::class,
],
```


Usage
------

Now any enum created based on `BenSampo\Enum\Enum` can be transformed to typescript like below

```php
final class UserType extends Enum
{
    const Administrator = 0;
    const Moderator = 1;
    const Subscriber = 2;
    const SuperAdministrator = 3;
}
```

```typescript type
export type UserType = {
    Administrator = 0,
    Moderator = 1,
    Subscriber = 2,
    SuperAdministrator = 3,
}
```

Or transform to enums: 

This must be enabled in `config/type-script-transformer`

`'transform_to_native_enums' => true,`

```typescript enum
export enum UserType {
    Administrator = 0,
    Moderator = 1,
    Subscriber = 2,
    SuperAdministrator = 3,
}
```

Read the [documentation](https://spatie.be/docs/typescript-transformer/v1/introduction) for further details.

License
-------
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.