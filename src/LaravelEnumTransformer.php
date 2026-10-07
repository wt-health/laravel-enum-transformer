<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer;

use Spatie\TypeScriptTransformer\Data\TransformationContext;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;
use Spatie\TypeScriptTransformer\Transformers\EnumProviders\EnumProvider;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnion;
use Webtools\LaravelEnumTransformer\EnumProviders\BenSampoEnumProvider;
use Webtools\LaravelEnumTransformer\TypeScriptNodes\TypeScriptEnumDeclaration;
use Webtools\LaravelEnumTransformer\TypeScriptNodes\TypeScriptJsonLiteral;

/**
 * Transforms bensampo/laravel-enum classes into TypeScript (spatie/typescript-transformer v3).
 *
 * - `new LaravelEnumTransformer()` (default) writes a union type: `export type Roles = "Master" | "Admin";`
 * - `new LaravelEnumTransformer(useUnionEnums: false)` writes a native enum, the v3 replacement
 *   for the old `transform_to_native_enums => true` config option:
 *
 *   export enum Roles {
 *     MASTER = "Master",
 *     ADMIN = "Admin",
 *   }
 */
class LaravelEnumTransformer extends EnumTransformer
{
    public function __construct(
        bool $useUnionEnums = true,
        EnumProvider $enumProvider = new BenSampoEnumProvider,
    ) {
        parent::__construct($useUnionEnums, $enumProvider);
    }

    public function transform(PhpClassNode $phpClassNode, TransformationContext $context): Transformed|Untransformable
    {
        // Spatie only validates the values for unions, but native TypeScript enum members can
        // only be numbers or strings too, so skip enums with other values (bool, null, arrays)
        // instead of writing invalid TypeScript.
        if ($this->enumProvider->isEnum($phpClassNode) && ! $this->enumProvider->isValidUnion($phpClassNode)) {
            return Untransformable::create();
        }

        return parent::transform($phpClassNode, $context);
    }

    /**
     * @param  list<array{name: string, value: mixed}>  $cases
     */
    protected function transformAsNativeEnum(string $name, array $cases): TypeScriptNode
    {
        return new TypeScriptEnumDeclaration($name, $cases);
    }

    /**
     * @param  list<array{name: string, value: mixed}>  $cases
     */
    protected function transformAsUnion(string $name, array $cases): TypeScriptNode
    {
        return new TypeScriptAlias(
            new TypeScriptIdentifier($name),
            new TypeScriptUnion(array_map(
                fn (array $case) => new TypeScriptJsonLiteral($case['value']),
                $cases,
            )),
        );
    }
}
