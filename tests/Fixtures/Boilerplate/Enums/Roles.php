<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\Tests\Fixtures\Boilerplate\Enums;

use BenSampo\Enum\Enum;

/**
 * Mirrors App\Enums\Roles from laravel-react-boilerplate-v2.
 *
 * @extends Enum<string>
 */
final class Roles extends Enum
{
    public const MASTER = 'Master';

    public const ADMIN = 'Admin';

    public const USER = 'User';
}
