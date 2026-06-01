<?php

declare(strict_types=1);

namespace Capell\Tests\PHPStan;

use Capell\Blog\Support\Loader\TagLoader;
use Capell\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

final class TagLoaderGetTagsDynamicStaticMethodReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return TagLoader::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getTags';
    }

    public function getTypeFromStaticMethodCall(
        MethodReflection $methodReflection,
        StaticCall $methodCall,
        Scope $scope,
    ): Type {
        $collectionType = new GenericObjectType(Collection::class, [
            new IntegerType,
            new ObjectType(Tag::class),
        ]);
        $paginatorType = new GenericObjectType(LengthAwarePaginator::class, [
            new IntegerType,
            new ObjectType(Tag::class),
        ]);

        $withPagination = $this->withPaginationArgument($methodCall);

        if (! $withPagination instanceof Expr) {
            return $collectionType;
        }

        $withPaginationType = $scope->getType($withPagination);

        if ($withPaginationType->isTrue()->yes()) {
            return $paginatorType;
        }

        if ($withPaginationType->isFalse()->yes()) {
            return $collectionType;
        }

        return TypeCombinator::union($collectionType, $paginatorType);
    }

    private function withPaginationArgument(StaticCall $methodCall): ?Expr
    {
        foreach ($methodCall->getArgs() as $position => $argument) {
            if ($argument->name?->toString() === 'withPagination') {
                return $argument->value;
            }

            if ($argument->name === null && $position === 5) {
                return $argument->value;
            }
        }

        return null;
    }
}
