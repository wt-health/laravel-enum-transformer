<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums;

use BenSampo\Enum\Enum;

/**
 * @extends Enum<string>
 */
abstract class AbstractEnum extends Enum
{
    public const SHARED = 'shared';
}
