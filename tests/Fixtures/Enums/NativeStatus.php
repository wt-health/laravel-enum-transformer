<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\Tests\Fixtures\Enums;

enum NativeStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
