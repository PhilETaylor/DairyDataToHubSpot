<?php

declare(strict_types=1);

/*
 * @author    Phil E. Taylor <phil@phil-taylor.com>
 * @copyright Copyright (C) 2024 Red Evolution Limited.
 * @license   GPL
 */

use Rector\CodeQuality\Rector\BooleanAnd\RemoveUselessIsObjectCheckRector;
use Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Rector\CodeQuality\Rector\Class_\StaticToSelfStaticMethodCallOnFinalClassRector;
use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\CompleteMissingIfElseBracketRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\NullsafeMethodCall\CleanupUnneededNullsafeOperatorRector;
use Rector\CodeQuality\Rector\Switch_\SwitchTrueToIfRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\CodingStyle\Rector\ClassConst\SplitGroupedClassConstantsRector;
use Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\CodingStyle\Rector\Stmt\RemoveUselessAliasInUseStatementRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveNullTagValueNodeRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnExprInConstructRector;
use Rector\DeadCode\Rector\Property\RemoveUselessReadOnlyTagRector;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\CodeQuality\Rector\BinaryOp\ResponseStatusCodeRector;
use Rector\Symfony\CodeQuality\Rector\ClassMethod\ActionSuffixRemoverRector;
use Rector\Symfony\CodeQuality\Rector\ClassMethod\ResponseReturnTypeControllerActionRector;
use Rector\Symfony\CodeQuality\Rector\ClassMethod\TemplateAnnotationToThisRenderRector;
use Rector\Symfony\CodeQuality\Rector\MethodCall\LiteralGetToRequestClassConstantRector;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Set\TwigSetList;
use Rector\Symfony\Symfony27\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector;
use Rector\Symfony\Symfony30\Rector\ClassMethod\FormTypeGetParentRector;
use Rector\Symfony\Symfony30\Rector\ClassMethod\GetRequestRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\FormTypeInstanceToClassConstRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\ReadOnlyOptionToAttributeRector;
use Rector\Symfony\Symfony33\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector;
use Rector\Symfony\Symfony34\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector;
use Rector\Symfony\Symfony34\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector;
use Rector\Symfony\Symfony42\Rector\MethodCall\ContainerGetToConstructorInjectionRector;
use Rector\Symfony\Symfony42\Rector\New_\RootNodeTreeBuilderRector;
use Rector\Symfony\Symfony43\Rector\MethodCall\MakeDispatchFirstArgumentEventRector;
use Rector\Symfony\Symfony44\Rector\ClassMethod\ConsoleExecuteReturnIntRector;
use Rector\Symfony\Symfony44\Rector\MethodCall\AuthorizationCheckerIsGrantedExtractorRector;
use Rector\Symfony\Symfony52\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector;
use Rector\Symfony\Symfony60\Rector\MethodCall\GetHelperControllerToServiceRector;
use Rector\Symfony\Twig134\Rector\Return_\SimpleFunctionAndFilterRector;
use Rector\Transform\Rector\String_\StringToClassConstantRector;
use Rector\TypeDeclaration\Rector\Class_\AddTestsVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\Class_\MergeDateTimePropertyTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Attribute\Route as NewRoute;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/public_html']);

    $rectorConfig->rules([
        ActionSuffixRemoverRector::class,
        AuthorizationCheckerIsGrantedExtractorRector::class,
        BinaryFileResponseCreateToNewInstanceRector::class,
        ChangeArrayPushToArrayAssignRector::class,
        ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector::class,
        ChangeIfElseValueAssignToEarlyReturnRector::class,
        ChangeNestedForeachIfsToEarlyContinueRector::class,
        ChangeStringCollectionOptionToConstantRector::class,
        CombineIfRector::class,
        ConsoleExceptionToErrorEventConstantRector::class,
        ConsoleExecuteReturnIntRector::class,
        ContainerGetToConstructorInjectionRector::class,
        CountArrayToEmptyArrayComparisonRector::class,
        StringToClassConstantRector::class,
        FormTypeGetParentRector::class,
        FormTypeInstanceToClassConstRector::class,
        FuncGetArgsToVariadicParamRector::class,
        GetHelperControllerToServiceRector::class,
        GetRequestRector::class,
        InlineIfToExplicitIfRector::class,
        LiteralGetToRequestClassConstantRector::class,
        MakeDispatchFirstArgumentEventRector::class,
        MakeInheritedMethodVisibilitySameAsParentRector::class,
        MergeMethodAnnotationToRouteAnnotationRector::class,
        PreparedValueToEarlyReturnRector::class,
        ReadOnlyOptionToAttributeRector::class,
        RemoveAlwaysElseRector::class,
        ReplaceSensioRouteAnnotationWithSymfonyRector::class,
        ResponseReturnTypeControllerActionRector::class,
        ResponseStatusCodeRector::class,
        RootNodeTreeBuilderRector::class,
        ShortenElseIfRector::class,
        SimpleFunctionAndFilterRector::class,
        SimplifyEmptyArrayCheckRector::class,
        SimplifyIfElseToTernaryRector::class,
        SimplifyIfReturnBoolRector::class,
        SimplifyRegexPatternRector::class,
        SimplifyStrposLowerRector::class,
        SimplifyUselessVariableRector::class,
        SplitGroupedClassConstantsRector::class,
        TemplateAnnotationToThisRenderRector::class,
        UnnecessaryTernaryExpressionRector::class,
        UnusedForeachValueToArrayKeysRector::class,
        IfIssetToCoalescingRector::class,
        CleanupUnneededNullsafeOperatorRector::class,
        SwitchTrueToIfRector::class,
        CompleteMissingIfElseBracketRector::class,
        RemoveUselessReturnExprInConstructRector::class,
        RemoveNullTagValueNodeRector::class,
        RemoveParentCallWithoutParentRector::class,
        RemoveUnusedConstructorParamRector::class,
        RemoveUnusedPromotedPropertyRector::class,
        RemoveUselessParamTagRector::class,
        MergeDateTimePropertyTypeDeclarationRector::class,
        RemoveUselessIsObjectCheckRector::class,
        AddOverrideAttributeToOverriddenMethodsRector::class,
        RemoveUselessAliasInUseStatementRector::class,
        RemoveUnusedVariableAssignRector::class,
        // https://github.com/rectorphp/rector/releases/tag/1.0.1
        AddTestsVoidReturnTypeWhereNoReturnRector::class,
        StaticToSelfStaticMethodCallOnFinalClassRector::class,
        // https://github.com/rectorphp/rector/releases/tag/1.0.4
        RemoveUselessReadOnlyTagRector::class,
        AddVoidReturnTypeWhereNoReturnRector::class,
    ]);

    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        Route::class => NewRoute::class,
    ]);

    $rectorConfig->importNames();
    $rectorConfig->removeUnusedImports();

    $rectorConfig->skip([RemoveUnreachableStatementRector::class]);

    $rectorConfig->sets(
        [
            SetList::DEAD_CODE,
            PHPUnitSetList::PHPUNIT_90,
            TwigSetList::TWIG_240,
            TwigSetList::TWIG_UNDERSCORE_TO_NAMESPACE,
            SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
            SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
            SymfonySetList::SYMFONY_CODE_QUALITY,
            SymfonySetList::SYMFONY_64,
            LevelSetList::UP_TO_PHP_83,
            DoctrineSetList::DOCTRINE_CODE_QUALITY,
            DoctrineSetList::DOCTRINE_DBAL_40,
            DoctrineSetList::DOCTRINE_ORM_29,
            DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        ],
    );
};
