<?php
/*
 * @author    Phil E. Taylor <phil@phil-taylor.com>
 * @copyright Copyright (C) 2024 Red Evolution Limited.
 * @license   GPL
 */

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\ConstantNotation\NativeConstantInvocationFixer;
use PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\ReturnNotation\SimplifiedNullReturnFixer;
use PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer;
use Symplify\CodingStandard\Fixer\Annotation\RemovePHPStormAnnotationFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer;
use Symplify\CodingStandard\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer;
use Symplify\CodingStandard\Fixer\Commenting\RemoveUselessDefaultCommentFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\CodingStandard\Fixer\Spacing\StandaloneLinePromotedPropertyFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([
        SetList::PSR_12,
        SetList::DOCTRINE_ANNOTATIONS,
        SetList::DOCBLOCK,
        SetList::NAMESPACES,
        SetList::CLEAN_CODE,
        SetList::ARRAY,
        SetList::COMMENTS,
        SetList::PHPUNIT,
        SetList::SPACES,
        SetList::SYMPLIFY,
    ]);

    $ecsConfig->ruleWithConfiguration(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ]);

    $ecsConfig->ruleWithConfiguration(YodaStyleFixer::class, [
        'equal'            => false,
        'identical'        => false,
        'less_and_greater' => false,
    ]);

    $ecsConfig->ruleWithConfiguration(LineLengthFixer::class, [
        LineLengthFixer::LINE_LENGTH => 140,
    ]);

    $ecsConfig->ruleWithConfiguration(BinaryOperatorSpacesFixer::class, [
        'default' => 'align',
    ]);

    $ecsConfig->ruleWithConfiguration(HeaderCommentFixer::class, [
        'header'       => <<<EOF
@author    Phil E. Taylor <phil@phil-taylor.com>
@copyright Copyright (C) 2024 Red Evolution Limited.
@license   GPL
EOF
        ,
        'comment_type' => 'comment',
        'separate'     => 'bottom',
    ]);

    $ecsConfig->paths(['src', 'public_html', 'ecs.php', 'rector.php']);
    $ecsConfig->cacheDirectory(__DIR__ . '/.cache/ecs');
    $ecsConfig->parallel();

    $ecsConfig->rule(NativeConstantInvocationFixer::class);
    $ecsConfig->rule(NativeFunctionInvocationFixer::class);
    $ecsConfig->rule(StandaloneLinePromotedPropertyFixer::class);
    $ecsConfig->rule(LineLengthFixer::class);
    $ecsConfig->rule(StandaloneLineInMultilineArrayFixer::class);
    $ecsConfig->rule(RemoveUselessDefaultCommentFixer::class);
    $ecsConfig->rule(RemovePHPStormAnnotationFixer::class);
    $ecsConfig->rule(ParamReturnAndVarTagMalformsFixer::class);
    $ecsConfig->rule(SimplifiedNullReturnFixer::class);
    $ecsConfig->rule(ProtectedToPrivateFixer::class);
    $ecsConfig->rule(SemicolonAfterInstructionFixer::class);
    $ecsConfig->rule(BlankLineAfterOpeningTagFixer::class);
    $ecsConfig->rule(NoSuperfluousPhpdocTagsFixer::class);
    $ecsConfig->rule(NoSuperfluousElseifFixer::class);

    $ecsConfig->rule(\ErickSkrauch\PhpCsFixer\Fixer\FunctionNotation\AlignMultilineParametersFixer::class);
    $ecsConfig->rule(\ErickSkrauch\PhpCsFixer\Fixer\Whitespace\BlankLineBeforeReturnFixer::class);
};
