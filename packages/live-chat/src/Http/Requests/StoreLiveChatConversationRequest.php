<?php

declare(strict_types=1);

namespace Capell\LiveChat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreLiveChatConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:' . $this->configInt('capell-live-chat.max_message_length', 4000)],
            'visitor_token' => ['nullable', 'string', 'max:255'],
            'flow' => ['nullable', Rule::in(['message_first', 'details_first'])],
            'timezone' => ['nullable', 'timezone'],
            'locale' => ['nullable', 'string', 'max:12'],
            'visitor' => ['nullable', 'array'],
            'visitor.name' => ['nullable', 'string', 'max:255'],
            'visitor.email' => ['nullable', 'email:rfc', 'max:255'],
            'visitor.phone' => ['nullable', 'string', 'max:80'],
            'visitor.company' => ['nullable', 'string', 'max:255'],
            'visitor.topic' => ['nullable', 'string', 'max:255'],
            'visitor.preferred_callback_at' => ['nullable', 'date'],
            'visitor.processing_consent' => ['nullable', 'boolean'],
            'visitor.marketing_consent' => ['nullable', 'boolean'],
            'page' => ['nullable', 'array'],
            'page.url' => ['nullable', 'url', 'max:2048'],
            'page.referrer' => ['nullable', 'url', 'max:2048'],
            'attachments' => ['nullable', 'array', 'max:' . $this->configInt('capell-live-chat.max_attachment_count', 5)],
            'attachments.*' => [
                'file',
                'max:' . $this->configInt('capell-live-chat.attachments.max_kilobytes', 10240),
                'mimes:' . implode(',', $this->attachmentMimes()),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function attachmentMimes(): array
    {
        $mimes = config('capell-live-chat.attachments.mimes', []);

        return is_array($mimes)
            ? array_values(array_filter($mimes, static fn (mixed $mime): bool => is_string($mime) && $mime !== ''))
            : ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'txt', 'csv', 'doc', 'docx'];
    }

    private function configInt(string $key, int $fallback): int
    {
        $value = config($key);

        return is_int($value) || (is_string($value) && ctype_digit($value)) ? (int) $value : $fallback;
    }
}
