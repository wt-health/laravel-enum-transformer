<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\Tests\Support;

use Closure;
use Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerApplicationServiceProvider;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

/**
 * Stands in for the App\Providers\TypeScriptTransformerServiceProvider an application would have.
 */
class TypeScriptTransformerTestServiceProvider extends TypeScriptTransformerApplicationServiceProvider
{
    /** @var (Closure(TypeScriptTransformerConfigFactory): void)|null */
    public static ?Closure $configure = null;

    protected function configure(TypeScriptTransformerConfigFactory $config): void
    {
        if (self::$configure !== null) {
            (self::$configure)($config);
        }
    }
}
