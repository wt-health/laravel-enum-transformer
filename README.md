Laravel Enum Transformer
================================

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

```typescript
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