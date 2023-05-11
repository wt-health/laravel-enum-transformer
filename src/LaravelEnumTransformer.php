<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer;

use BenSampo\Enum\Enum;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class LaravelEnumTransformer implements Transformer
{
    public function __construct(protected TypeScriptTransformerConfig $config)
    {
    }
    
    public function transform(ReflectionClass $class, string $name): TransformedType
    {
        if ($class->isSubclassOf(Enum::class) === false) {
            return null;   
        }
        
        return $this->config->shouldTransformToNativeEnums()
            ? $this->toEnum($class, $name)
            : $this->toType($class, $name);
    }

    private function resolveOptionsToEnum(ReflectionClass $class): string
    {
        /** @var Enum $enum */
        $enum = $class->getName();

        $options = array_map(
            fn ($key) => "  {$key} = " . json_encode($enum::getValue($key)) . ',',
            $enum::getKeys()
        );

        return implode(PHP_EOL, $options);
    }

    private function resolveOptionsToType(ReflectionClass $class): string
    {
        /** @var Enum $enum */
        $enum = $class->getName();

        $options = array_map(
            fn ($key) => "  {$key}: " . json_encode($enum::getValue($key)) . ',',
            $enum::getKeys()
        );

        return implode(PHP_EOL, $options);
    }

    protected function toEnum(ReflectionClass $class, string $name): TransformedType
    {
        return TransformedType::create(
            $class,
            $name,
            PHP_EOL . $this->resolveOptionsToEnum($class) . PHP_EOL,
            keyword: 'enum'
        );
    }

    private function toType(ReflectionClass $class, string $name): TransformedType
    {
        return TransformedType::create(
            $class,
            $name,
            '{' . PHP_EOL . $this->resolveOptionsToType($class) . PHP_EOL . '}',
            keyword: 'type'
        );
    }
}
