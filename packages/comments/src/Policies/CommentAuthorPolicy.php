<?php

declare(strict_types=1);

namespace Capell\Comments\Policies;

use Capell\Admin\Support\SiteScope;
use Capell\Comments\Models\CommentAuthor;
use Capell\Core\Models\Site;
use Illuminate\Contracts\Auth\Authenticatable;

class CommentAuthorPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    public function view(Authenticatable $user, CommentAuthor $author): bool
    {
        return $author->site instanceof Site && SiteScope::actorCanUseSite($user, $author->site);
    }

    public function update(Authenticatable $user, CommentAuthor $author): bool
    {
        return $this->view($user, $author);
    }
}
