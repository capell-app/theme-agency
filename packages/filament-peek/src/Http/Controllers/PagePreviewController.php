<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Http\Controllers;

use Capell\FilamentPeek\Actions\CreatePagePreviewSnapshotAction;
use Capell\FilamentPeek\Actions\RenderPagePreviewSnapshotAction;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class PagePreviewController extends Controller
{
    public function __invoke(string $token): Response
    {
        $snapshot = resolve(CreatePagePreviewSnapshotAction::class)->find($token);

        abort_unless($snapshot !== null, 404);
        abort_unless($this->userOwnsSnapshot($snapshot->userId), 403);

        $response = RenderPagePreviewSnapshotAction::run($snapshot);
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }

    private function userOwnsSnapshot(int|string $snapshotUserId): bool
    {
        $user = Filament::auth()->user();

        if (! $user instanceof Model) {
            $user = Auth::user();
        }

        if (! $user instanceof Model) {
            return false;
        }

        return (string) $user->getAuthIdentifier() === (string) $snapshotUserId;
    }
}
