<?php


declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withAutoloadPaths([__DIR__ . '/../Classes'])
    ->withCache('.cache/rector/upgrade/')
    ->withImportNames(false)
    ->withPhpSets(true)
    ->withPaths([
        __DIR__ . '/../Classes',
        __DIR__ . '/../Tests',
        __DIR__ . '/../Configuration',
        __DIR__ . '/../Resources',
        __DIR__ . '/../code-quality',

        // add all directories containing php files
    ])
    ->withSets([
        SetList::PHP_84,

        // add custom sets here
    ])
    ->withRules([
        // add custom rules here
    ])
    ->withSkip([
        AddOverrideAttributeToOverriddenMethodsRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
        ReadOnlyPropertyRector::class,
    ]);
