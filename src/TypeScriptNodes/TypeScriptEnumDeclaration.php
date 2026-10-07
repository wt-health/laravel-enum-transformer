<?php

declare(strict_types=1);

namespace Webtools\LaravelEnumTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNamedNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;

/**
 * A native TypeScript enum, written exactly like 1.x of this package did:
 * two space indentation and JSON encoded values (`ADMIN = "Admin",`).
 */
class TypeScriptEnumDeclaration implements TypeScriptNamedNode, TypeScriptNode
{
    /**
     * @param  list<array{name: string, value: mixed}>  $cases
     */
    public function __construct(
        public string $name,
        public array $cases,
    ) {}

    public function write(WritingContext $context): string
    {
        $output = 'enum '.$this->name.' {'."\n";

        foreach ($this->cases as $case) {
            $output .= "  {$case['name']} = ".TypeScriptJsonLiteral::encode($case['value']).",\n";
        }

        return $output.'}';
    }

    public function getName(): string
    {
        return $this->name;
    }
}
