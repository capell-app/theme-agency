<?php

declare(strict_types=1);

namespace Capell\UrlManager\Enums;

enum UrlManagerPermission: string
{
    case ViewRedirectRulesPage = 'View:RedirectRulesPage';
    case ManageRedirectRules = 'Manage:RedirectRules';
    case ViewNotFoundOpportunitiesPage = 'View:NotFoundOpportunitiesPage';
    case ManageNotFoundOpportunities = 'Manage:NotFoundOpportunities';

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_map(
            static fn (self $permission): string => $permission->value,
            self::cases(),
        );
    }
}
