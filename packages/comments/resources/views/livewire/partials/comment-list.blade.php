<ol>
    @foreach ($comments as $comment)
        <li>
            <article>
                <header>
                    <strong>{{ $comment->authorName }}</strong>
                    <time
                        datetime="{{ $comment->submittedAt->toAtomString() }}"
                    >
                        {{ $comment->submittedAt->diffForHumans() }}
                    </time>
                </header>
                <p>{{ $comment->body }}</p>
                <button
                    type="button"
                    wire:click="replyTo('{{ $comment->publicId }}')"
                >
                    {{ __('capell-comments::generic.reply') }}
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
