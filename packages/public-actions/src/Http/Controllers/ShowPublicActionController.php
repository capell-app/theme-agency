<?php

declare(strict_types=1);

namespace Capell\PublicActions\Http\Controllers;

use Capell\PublicActions\Actions\SubmitPublicActionAction;
use Capell\PublicActions\Enums\PublicActionStatus;
use Capell\PublicActions\Models\PublicAction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ShowPublicActionController
{
    public function __construct(
        private readonly SubmitPublicActionAction $submitPublicAction,
    ) {}

    public function __invoke(Request $request, string $action): Response
    {
        $publicAction = $this->submitPublicAction->resolve($action, $request);

        abort_unless($publicAction->status === PublicActionStatus::Active, 404);

        abort_unless((bool) data_get($publicAction->settings, 'public_page_enabled', false), 404);

        return $this->noStore(response()->view('capell-public-actions::action', [
            'action' => $publicAction,
            'fields' => $this->fields($publicAction),
        ]));
    }

    private function noStore(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * @return list<array{key: string, label: string, type: string, required: bool}>
     */
    private function fields(PublicAction $action): array
    {
        $fields = data_get($action->payload_schema, 'fields', []);

        if (! is_array($fields)) {
            return [];
        }

        return array_values(collect($fields)
            ->filter(fn (mixed $field): bool => is_array($field) && is_string($field['key'] ?? null))
            ->map(function (array $field): array {
                $key = $field['key'];
                $type = is_string($field['type'] ?? null) ? $field['type'] : 'text';
                $label = is_string($field['label'] ?? null) ? $field['label'] : str($key)->headline()->toString();

                return [
                    'key' => $key,
                    'label' => $label,
                    'type' => in_array($type, ['email', 'text', 'url', 'tel'], true) ? $type : 'text',
                    'required' => (bool) ($field['required'] ?? false),
                ];
            })
            ->values()
            ->all());
    }
}
