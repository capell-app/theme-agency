<?php

declare(strict_types=1);

namespace Capell\LiveChat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

final class RequestLiveChatHandoffRequest extends FormRequest
{
    #[Override]
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:1000'],
            'visitor_token' => ['nullable', 'string', 'max:255'],
            'visitor' => ['nullable', 'array'],
            'visitor.name' => ['nullable', 'string', 'max:255'],
            'visitor.email' => ['nullable', 'email:rfc', 'max:255'],
            'visitor.phone' => ['nullable', 'string', 'max:80'],
            'visitor.company' => ['nullable', 'string', 'max:255'],
        ];
    }
}
