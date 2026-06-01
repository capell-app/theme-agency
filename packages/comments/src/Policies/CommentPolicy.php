<?php

declare(strict_types=1);

namespace Capell\Comments\Policies;

use Capell\Admin\Support\SiteScope;
use Capell\Comments\Models\Comment;
use Capell\Core\Models\Site;
use Illuminate\Contracts\Auth\Authenticatable;
use Throwable;

class CommentPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return $this->actorCanAccessAnyCommentSite($user);
    }

    public function view(Authenticatable $user, Comment $comment): bool
    {
        return $comment->site instanceof Site && SiteScope::actorCanUseSite($user, $comment->site);
    }

    public function update(Authenticatable $user, Comment $comment): bool
    {
        return $this->view($user, $comment);
    }

    public function approve(Authenticatable $user, Comment $comment): bool
    {
        return $this->update($user, $comment);
    }

    public function delete(Authenticatable $user, Comment $comment): bool
    {
        return $this->update($user, $comment);
    }

    private function actorCanAccessAnyCommentSite(Authenticatable $user): bool
    {
        if (SiteScope::isGlobalActor($user)) {
            return true;
        }

        try {
            return $user->getAssignedSiteIds()->isNotEmpty();
        } catch (Throwable) {
            return false;
        }
    }
}
