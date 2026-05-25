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
use Throwable;

final class PagePreviewController extends Controller
{
    public function __invoke(string $token): Response
    {
        $snapshot = resolve(CreatePagePreviewSnapshotAction::class)->find($token);

        if ($snapshot === null) {
            return $this->errorResponse(
                404,
                __('capell-filament-peek::errors.expired_title'),
                __('capell-filament-peek::errors.expired_body'),
            );
        }

        if (! $this->userOwnsSnapshot($snapshot->userId)) {
            return $this->errorResponse(
                403,
                __('capell-filament-peek::errors.forbidden_title'),
                __('capell-filament-peek::errors.forbidden_body'),
            );
        }

        try {
            $response = RenderPagePreviewSnapshotAction::run($snapshot);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->errorResponse(
                500,
                __('capell-filament-peek::errors.render_title'),
                __('capell-filament-peek::errors.render_body'),
            );
        }

        $this->makePrivate($response);

        return $response;
    }

    private function errorResponse(int $status, string $title, string $body): Response
    {
        $response = response()
            ->view('capell-filament-peek::preview-error', [
                'title' => $title,
                'body' => $body,
            ], $status);

        $this->makePrivate($response);

        return $response;
    }

    private function makePrivate(Response $response): void
    {
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
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
