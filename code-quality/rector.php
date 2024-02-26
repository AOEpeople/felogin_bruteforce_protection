<?php

declare(strict_types=1);

use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\Php81\Rector\ClassConst\FinalizePublicClassConstantRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictSetUpRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths(
        [
            __DIR__ . '/../Classes',
            __DIR__ . '/../Tests',
            __DIR__ . '/../code-quality',
        ]
    );
    if (strpos(PHP_VERSION, '7.4') === 0) {
        $rectorConfig->phpVersion(Rector\Core\ValueObject\PhpVersion::PHP_74);
    }

    if (strpos(PHP_VERSION, '8.0') === 0) {
        $rectorConfig->phpVersion(Rector\Core\ValueObject\PhpVersion::PHP_80);
    }

    if (strpos(PHP_VERSION, '8.1') === 0) {
        $rectorConfig->phpVersion(Rector\Core\ValueObject\PhpVersion::PHP_81);
    }

    if (strpos(PHP_VERSION, '8.2') === 0) {
        $rectorConfig->phpVersion(Rector\Core\ValueObject\PhpVersion::PHP_82);
    }

    $rectorConfig->sets(
        [
            SetList::CODE_QUALITY,
            SetList::CODING_STYLE,
            SetList::DEAD_CODE,
            SetList::EARLY_RETURN,
            SetList::PHP_74,
            SetList::PHP_80,
            SetList::PHP_81,
            SetList::PHP_82,
            SetList::PRIVATIZATION,
            SetList::TYPE_DECLARATION,
            SetList::MYSQL_TO_MYSQLI,
            SetList::NAMING,
        ]
    );

    $rectorConfig->import(PHPUnitSetList::PHPUNIT_CODE_QUALITY);
    $rectorConfig->importNames(false);
    $rectorConfig->autoloadPaths([__DIR__ . '/../Classes']);
    $rectorConfig->cacheDirectory('.cache/rector/default/');

    $rectorConfig->skip(
        [
            RecastingRemovalRector::class,
            PostIncDecToPreIncDecRector::class,
            FinalizeClassesWithoutChildrenRector::class,
            ChangeAndIfToEarlyReturnRector::class,
            IssetOnPropertyObjectToPropertyExistsRector::class,
            FlipTypeControlToUseExclusiveTypeRector::class,
            RenameVariableToMatchNewTypeRector::class,
            AddLiteralSeparatorToNumberRector::class,
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
            FinalizePublicClassConstantRector::class,
            ClassPropertyAssignToConstructorPromotionRector::class,
        ]
    );

    $rectorConfig->rule(RemoveUnusedPrivatePropertyRector::class);
};
