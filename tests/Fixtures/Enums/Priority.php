<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums;

use BenSampo\Enum\Enum;

/**
 * @extends Enum<int>
 */
final class Priority extends Enum
{
    public const LOW = 0;

    public const MEDIUM = 10;

    public const HIGH = 20;
}
