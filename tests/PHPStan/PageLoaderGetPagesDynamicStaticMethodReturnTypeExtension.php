<?php

declare(strict_types=1);

namespace Capell\Tests\PHPStan;

use Capell\Core\Contracts\Pageable;
use Capell\Frontend\Support\Loader\PageLoader;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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

final class PageLoaderGetPagesDynamicStaticMethodReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return PageLoader::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getPages';
    }

    public function getTypeFromStaticMethodCall(
        MethodReflection $methodReflection,
        StaticCall $methodCall,
        Scope $scope,
    ): Type {
        $collectionType = $this->collectionType();
        $paginatorType = $this->paginatorType();

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

            if ($argument->name === null && $position === 14) {
                return $argument->value;
            }
        }

        return null;
    }

    private function collectionType(): GenericObjectType
    {
        return new GenericObjectType(Collection::class, [
            new IntegerType,
            $this->pageModelType(),
        ]);
    }

    private function paginatorType(): GenericObjectType
    {
        return new GenericObjectType(LengthAwarePaginator::class, [
            new IntegerType,
            $this->pageModelType(),
        ]);
    }

    private function pageModelType(): Type
    {
        return TypeCombinator::intersect(
            new ObjectType(Model::class),
            new GenericObjectType(Pageable::class, [new ObjectType(Model::class)]),
        );
    }
}
