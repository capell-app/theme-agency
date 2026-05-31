<?php

declare(strict_types=1);

namespace Capell\Tests\PHPStan;

use function array_map;

use Illuminate\Database\Eloquent\Factories\Factory;

use function in_array;

use Larastan\Larastan\Types\Factory\ModelFactoryType;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

final class FactoryNewDynamicStaticMethodReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return Factory::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), ['new', 'times'], true);
    }

    public function getTypeFromStaticMethodCall(
        MethodReflection $methodReflection,
        StaticCall $methodCall,
        Scope $scope,
    ): Type {
        if ($methodReflection->getName() === 'times' && $methodCall->getArgs() === []) {
            return new ErrorType;
        }

        $calledOnType = $methodCall->class instanceof Name
            ? $scope->resolveTypeByName($methodCall->class)
            : $scope->getType($methodCall->class);

        $isSingleModel = $methodReflection->getName() === 'new'
            ? TrinaryLogic::createYes()
            : TrinaryLogic::createNo();

        return TypeCombinator::union(...array_map(
            fn (ClassReflection $classReflection): ModelFactoryType => new ModelFactoryType(
                $classReflection->getName(),
                null,
                $classReflection,
                $isSingleModel,
            ),
            $calledOnType->getObjectClassReflections(),
        ));
    }
}
