<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;

/**
 * A literal value written with json_encode, as 1.x of this package did (`"foobar"`, `10`).
 */
class TypeScriptJsonLiteral implements TypeScriptNode
{
    public function __construct(public mixed $value) {}

    public function write(WritingContext $context): string
    {
        return self::encode($this->value);
    }

    public static function encode(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
