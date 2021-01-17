<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $services = $containerConfigurator->services();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $parameters->set(Option::SETS, [
        SetList::CLEAN_CODE,
        SetList::SYMFONY,
        SetList::PSR_12,
        SetList::PHPUNIT,
    ]);

    $services->set(PhpUnitMethodCasingFixer::class)->call('configure', [[
        'case' => 'snake_case',
    ]]);

    $services->set(PhpdocAlignFixer::class)->call('configure', [[
        'align' => 'left',
    ]]);

    $services->set(DeclareStrictTypesFixer::class);
};
