<?php

declare(strict_types=1);

namespace Capell\UrlManager\Listeners;

use Capell\Core\Events\PageUrlChanged;
use Capell\UrlManager\Actions\RecordChangedUrlRedirectAction;
use Capell\UrlManager\Data\ChangedUrlRedirectData;

final class RecordRedirectForChangedPageUrl
{
    public function handle(PageUrlChanged $event): void
    {
        RecordChangedUrlRedirectAction::run(new ChangedUrlRedirectData(
            previousUrl: $event->old_url,
            currentUrl: $event->new_url,
            siteId: $event->site_id,
            languageId: $event->language_id,
            notes: __('capell-url-manager::generic.changed_url_redirect_note'),
        ));
    }
}
