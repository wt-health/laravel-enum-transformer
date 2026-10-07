<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums;

use BenSampo\Enum\Enum;

/**
 * Overrides the getKeys()/getValue() hooks that 1.x read the enum through.
 *
 * @extends Enum<string>
 */
final class OverriddenHooks extends Enum
{
    public const VISIBLE = 'visible';

    public const INTERNAL = 'internal';

    public static function getKeys(mixed $values = null): array
    {
        return array_values(array_diff(parent::getKeys($values), ['INTERNAL']));
    }

    public static function getValue(string $key): mixed
    {
        return strtoupper((string) parent::getValue($key));
    }
}
