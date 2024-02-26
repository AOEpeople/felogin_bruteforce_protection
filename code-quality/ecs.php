<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\FunctionNotation\FunctionTypehintSpaceFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\Fixer\LineLength\DocBlockLineLengthFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths(
        [
            __DIR__ . '/../Classes',
            __DIR__ . '/../Tests',
            __DIR__ . '/../code-quality',
        ]
    );

    $ecsConfig->import(SetList::COMMON);
    $ecsConfig->import(SetList::CLEAN_CODE);
    $ecsConfig->import(SetList::PSR_12);
    $ecsConfig->import(SetList::SYMPLIFY);

    $ecsConfig->services()
        ->set(LineLengthFixer::class)
        ->call('configure', [[
            LineLengthFixer::LINE_LENGTH => 140,
            LineLengthFixer::INLINE_SHORT_LINES => false,
        ]]);

    // Skip Rules and Sniffer
    $ecsConfig->skip(
        [
            // Default Skips
            Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer::class => [
                __DIR__ . '/ecs.php',
            ],
            ArrayListItemNewlineFixer::class => null,
            ArrayOpenerAndCloserNewlineFixer::class => null,
            ClassAttributesSeparationFixer::class => null,
            OrderedImportsFixer::class => null,
            NotOperatorWithSuccessorSpaceFixer::class => null,
            ExplicitStringVariableFixer::class => null,
            ArrayIndentationFixer::class => null,
            DocBlockLineLengthFixer::class => null,
            '\SlevomatCodingStandard\Sniffs\Whitespaces\DuplicateSpacesSniff.DuplicateSpaces' => null,
            '\SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff.PartialUse' => null,

            // @todo for next upgrade
            NoSuperfluousPhpdocTagsFixer::class => null,
            FunctionTypehintSpaceFixer::class => [
                __DIR__ . '/../Classes/Hooks/UserAuth/PostUserLookUp.php',
            ],

            // @todo strict php
            DeclareStrictTypesFixer::class => null,
            StrictComparisonFixer::class => null,
            StrictParamFixer::class => null,
        ]
    );
};
