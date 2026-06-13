<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Contracts\LiveChatKnowledgeProvider;
use Capell\LiveChat\Data\LiveChatKnowledgeSearchResultData;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatKnowledgeDocument;
use Capell\LiveChat\Models\LiveChatMessage;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class SearchLiveChatKnowledgeDocumentsAction
{
    use AsAction;

    private const array STOP_WORDS = [
        'about',
        'after',
        'also',
        'and',
        'are',
        'can',
        'does',
        'for',
        'from',
        'how',
        'into',
        'our',
        'that',
        'the',
        'this',
        'what',
        'when',
        'where',
        'with',
        'you',
        'your',
    ];

    /**
     * @return list<LiveChatKnowledgeSearchResultData>
     */
    public function handle(LiveChatConversation $conversation, LiveChatMessage $message, int $limit = 3): array
    {
        $tokens = $this->tokens($message->body);

        if ($tokens === []) {
            return [];
        }

        $documents = $this->documents($conversation, $message);
        $results = [];

        foreach ($documents as $document) {
            $score = $this->score($document, $tokens);

            if ($score <= 0) {
                continue;
            }

            $results[] = new LiveChatKnowledgeSearchResultData($document, $score);
        }

        usort($results, static function (LiveChatKnowledgeSearchResultData $first, LiveChatKnowledgeSearchResultData $second): int {
            if ($first->score !== $second->score) {
                return $second->score <=> $first->score;
            }

            return $first->document->title <=> $second->document->title;
        });

        return array_slice($results, 0, max(1, $limit));
    }

    /**
     * @return list<string>
     */
    public function tokens(string $text): array
    {
        $parts = preg_split('/[^a-z0-9]+/i', Str::lower($text)) ?: [];
        $tokens = [];

        foreach ($parts as $part) {
            if (! is_string($part) || strlen($part) < 3 || in_array($part, self::STOP_WORDS, true)) {
                continue;
            }

            $tokens[] = $part;
        }

        return array_values(array_unique($tokens));
    }

    /**
     * @param  list<string>  $tokens
     */
    private function score(LiveChatKnowledgeDocument $document, array $tokens): int
    {
        $title = Str::lower($document->title);
        $content = Str::lower($document->content);
        $score = 0;

        foreach ($tokens as $token) {
            if (Str::contains($title, $token)) {
                $score += 4;
            }

            if (Str::contains($content, $token)) {
                $score += min(5, substr_count($content, $token));
            }
        }

        return $score;
    }

    /**
     * @return list<LiveChatKnowledgeDocument>
     */
    private function documents(LiveChatConversation $conversation, LiveChatMessage $message): array
    {
        $documents = [];
        $seenIds = [];

        foreach (app()->tagged(LiveChatKnowledgeProvider::TAG) as $provider) {
            if (! $provider instanceof LiveChatKnowledgeProvider) {
                continue;
            }

            foreach ($provider->documents($conversation, $message) as $document) {
                if (isset($seenIds[$document->id])) {
                    continue;
                }

                $seenIds[$document->id] = true;
                $documents[] = $document;
            }
        }

        return $documents;
    }
}
