<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums;

use BenSampo\Enum\Enum;

/**
 * A string that isn't valid UTF-8, which JSON encoding the output would choke on.
 *
 * @extends Enum<int|string>
 */
final class MalformedUtf8 extends Enum
{
    public const VALID = 'valid';

    public const BROKEN = "\xB1\x31";
}
