<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\EnumProviders;

use BenSampo\Enum\Enum;
use ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionClassConstant as RoaveReflectionClassConstant;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\Transformers\EnumProviders\EnumProvider;

/**
 * Teaches spatie/typescript-transformer v3 how to read bensampo/laravel-enum classes.
 *
 * It can be used on its own with Spatie's EnumTransformer
 * (`new EnumTransformer(enumProvider: new BenSampoEnumProvider())`), or through
 * LaravelEnumTransformer, which keeps the 1.x output format.
 */
class BenSampoEnumProvider implements EnumProvider
{
    /** JavaScript's Number.MAX_SAFE_INTEGER */
    private const MAX_SAFE_INTEGER = 9007199254740991;

    public function isEnum(PhpClassNode $phpClassNode): bool
    {
        if ($phpClassNode->isAbstract() || $phpClassNode->isInterface()) {
            return false;
        }

        return $phpClassNode->reflection->isSubclassOf(Enum::class);
    }

    public function isValidUnion(PhpClassNode $phpClassNode): bool
    {
        foreach ($this->resolveCases($phpClassNode) as $case) {
            if (! is_int($case['value']) && ! is_string($case['value'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<array{name: string, value: mixed}>
     */
    public function resolveCases(PhpClassNode $phpClassNode): array
    {
        $cases = [];

        foreach ($this->resolveConstants($phpClassNode) as $name => $value) {
            // TypeScript enum members and literal unions can only hold numbers and strings. Resolve
            // no cases for anything else (bool, null, arrays), so every EnumTransformer using this
            // provider, including Spatie's own in native mode, skips the enum instead of writing
            // invalid or misleading TypeScript.
            if (! $this->isRepresentable($value)) {
                return [];
            }

            $cases[] = ['name' => (string) $name, 'value' => $value];
        }

        return $cases;
    }

    /**
     * Strings must be valid UTF-8 (otherwise JSON encoding the output fails and aborts the whole
     * transform), and integers must fit JavaScript's safe range (otherwise they're rounded).
     */
    protected function isRepresentable(mixed $value): bool
    {
        return match (true) {
            is_string($value) => mb_check_encoding($value, 'UTF-8'),
            is_int($value) => abs($value) <= self::MAX_SAFE_INTEGER,
            default => false,
        };
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function resolveConstants(PhpClassNode $phpClassNode): array
    {
        if ($phpClassNode->reflection instanceof ReflectionClass) {
            /** @var class-string<Enum<mixed>> $enum */
            $enum = $phpClassNode->getName();

            // Read through getKeys()/getValue() exactly like 1.x, so enums overriding any of
            // getKeys(), getValue() or getConstants() keep generating the same output.
            $constants = [];

            foreach ($enum::getKeys() as $key) {
                $constants[$key] = $enum::getValue($key);
            }

            return $constants;
        }

        // In watch mode classes are reflected statically (roave/better-reflection), because PHP
        // can't re-load an edited class in the same process, so the constants are read from the
        // source. Overrides of getKeys()/getValue()/getConstants() can't run here; they apply
        // again on the next full `typescript:transform` run.
        return array_map(
            fn (RoaveReflectionClassConstant $constant): mixed => $constant->getValue(),
            $phpClassNode->reflection->getConstants(),
        );
    }
}
