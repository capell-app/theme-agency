<div class="space-y-4">
    <section>
        <h3 class="text-sm font-medium">
            {{ __('capell-comments::table.commentable') }}
        </h3>
        <p>{{ $commentableLabel }}</p>
        @if ($commentableUrl !== null)
            <p>
                <a
                    href="{{ $commentableUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    {{ $commentableUrl }}
                </a>
            </p>
        @endif
    </section>

    @if ($comment->parent !== null)
        <section>
            <h3 class="text-sm font-medium">
                {{ __('capell-comments::table.parent_comment') }}
            </h3>
            <p>{{ $comment->parent->body }}</p>
        </section>
    @endif

    <section>
        <h3 class="text-sm font-medium">
            {{ __('capell-comments::table.author_history') }}
        </h3>
        <p>
            {{ trans_choice('capell-comments::table.author_comment_count', $comment->author?->comments()->count() ?? 0) }}
        </p>
    </section>

    @if ($comment->moderationEvents->isNotEmpty())
        <section>
            <h3 class="text-sm font-medium">
                {{ __('capell-comments::table.moderation_history') }}
            </h3>
            <ol>
                @foreach ($comment->moderationEvents as $event)
                    <li>
                        {{ $event->occurred_at?->diffForHumans() }}:
                        {{ $event->action }}
                        @if ($event->note !== null)
                                - {{ $event->note }}
                        @endif
                    </li>
                @endforeach
            </ol>
        </section>
    @endif
</div>
