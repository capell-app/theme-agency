<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Http\Controllers;

use Capell\PrivacyCenter\Enums\CookieCategory;
use Illuminate\Contracts\View\View;

final class ShowConsentPreferencesController
{
    public function __invoke(): View
    {
        return view('capell-privacy-center::consent.preferences', [
            'categories' => CookieCategory::cases(),
        ]);
    }
}
