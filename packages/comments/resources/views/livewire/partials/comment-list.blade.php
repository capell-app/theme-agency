<ol>
    @foreach ($comments as $comment)
        <li>
            <article>
                <header>
                    <strong>{{ $comment->authorName }}</strong>
                    <time
                        datetime="{{ $comment->submittedAt->toAtomString() }}"
                    >
                        {{ $comment->submittedAtForHumans }}
                    </time>
                </header>
                <p>{{ $comment->body }}</p>
                <button
                    type="button"
                    wire:click="replyTo('{{ $comment->publicId }}')"
                >
                    {{ __('capell-comments::generic.reply') }}
                </button>
                <button
                    type="button"
                    wire:click="toggleReaction('{{ $comment->publicId }}')"
                    aria-label="{{ trans_choice('capell-comments::generic.reaction_count', $comment->reactionCount, ['count' => $comment->reactionCount]) }}"
                >
                    {{ __('capell-comments::generic.like') }}
                    <span>{{ $comment->reactionCount }}</span>
                </button>

                @if ($comment->children !== [])
                    @include('capell-comments::livewire.partials.comment-list', [
                        'comments' => $comment->children,
                    ])
                @endif

                @if ($comment->hasMoreReplies)
                    <button
                        type="button"
                        wire:click="loadMoreReplies('{{ $comment->publicId }}')"
                    >
                        {{ __('capell-comments::generic.load_more_replies') }}
                    </button>
                @endif
            </article>
        </li>
    @endforeach
</ol>
