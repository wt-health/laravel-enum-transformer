<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums;

use BenSampo\Enum\Enum;

/**
 * @extends Enum<int|string>
 */
final class MixedValues extends Enum
{
    public const ADMIN = 10;

    public const USER = 20;

    public const STRING_USER = 'foobar';
}
