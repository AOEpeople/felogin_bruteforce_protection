<?php

declare(strict_types=1);

use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictSetUpRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/../Classes',
        __DIR__ . '/../Tests',
        __DIR__ . '/../code-quality',
    ])
    ->withPhpSets(
        true,
        false
    )
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::PHP_80,
        SetList::PHP_81,
        SetList::PHP_82,
        SetList::PHP_83,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
        SetList::NAMING,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ])
    ->withRules([
        RemoveUnusedPrivatePropertyRector::class,
    ])
    ->withSkip([
        RecastingRemovalRector::class,
        IssetOnPropertyObjectToPropertyExistsRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        RenameVariableToMatchNewTypeRector::class,
        RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class,

        // @todo strict php
        ArgumentAdderRector::class,
        RemoveExtraParametersRector::class,
        EncapsedStringsToSprintfRector::class,
        WrapEncapsedVariableInCurlyBracesRector::class,
        UseIdenticalOverEqualWithSameTypeRector::class,
        TypedPropertyFromStrictSetUpRector::class,
        TypedPropertyFromAssignsRector::class,
        RenameVariableToMatchMethodCallReturnTypeRector::class,
        RenamePropertyToMatchTypeRector::class,
        YieldDataProviderRector::class,
        RenameParamToMatchTypeRector::class,

        // @todo on deprecation of 7.4
        StrStartsWithRector::class,

        // @todo on deprecation of 8.0
        ClassPropertyAssignToConstructorPromotionRector::class,
    ])
    ->withAutoloadPaths([__DIR__ . '/../Classes'])
    ->withCache('.cache/rector/default/')
    ->withImportNames(false);
