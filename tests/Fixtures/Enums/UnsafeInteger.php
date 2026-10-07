<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums;

use BenSampo\Enum\Enum;

/**
 * Integers outside JavaScript's safe range, which TypeScript would round.
 *
 * @extends Enum<int|string>
 */
final class UnsafeInteger extends Enum
{
    public const SAFE = 1;

    public const TOO_BIG = 9007199254740993;
}
