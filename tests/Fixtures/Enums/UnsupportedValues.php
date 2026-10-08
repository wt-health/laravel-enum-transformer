<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums;

use BenSampo\Enum\Enum;

/**
 * Values a TypeScript enum (or literal union) can't represent.
 *
 * @extends Enum<mixed>
 */
final class UnsupportedValues extends Enum
{
    public const ENABLED = true;

    public const EMPTY = null;
}
