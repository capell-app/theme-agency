<section
    class="capell-comments"
    aria-label="{{ __('capell-comments::generic.comments') }}"
>
    @if ($submitted)
        <p role="status">{{ __('capell-comments::messages.submitted') }}</p>
    @endif

    @if (isset($errors) && $errors->any())
        <div role="alert">
            <p>{{ __('capell-comments::messages.validation_failed') }}</p>
        </div>
    @endif

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
                        <ol>
                            @foreach ($comment->children as $reply)
                                <li>
                                    <article>
                                        <header>
                                            <strong>
                                                {{ $reply->authorName }}
                                            </strong>
                                            <time
                                                datetime="{{ $reply->submittedAt->toAtomString() }}"
                                            >
                                                {{ $reply->submittedAt->diffForHumans() }}
                                            </time>
                                        </header>
                                        <p>{{ $reply->body }}</p>
                                        <button
                                            type="button"
                                            wire:click="replyTo('{{ $reply->publicId }}')"
                                        >
                                            {{ __('capell-comments::generic.reply') }}
                                        </button>
                                    </article>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </article>
            </li>
        @endforeach
    </ol>

    <form wire:submit="submit">
        <div
            aria-hidden="true"
            style="
                position: absolute;
                left: -10000px;
                top: auto;
                width: 1px;
                height: 1px;
                overflow: hidden;
            "
        >
            <label>
                <span>{{ __('capell-comments::generic.website') }}</span>
                <input
                    type="text"
                    wire:model="commentWebsite"
                    name="website"
                    tabindex="-1"
                    autocomplete="off"
                />
            </label>
        </div>

        @if ($parentPublicId !== null)
            <p>
                {{ __('capell-comments::generic.replying') }}
                <button
                    type="button"
                    wire:click="cancelReply"
                >
                    {{ __('capell-comments::generic.cancel_reply') }}
                </button>
            </p>
        @endif

        @guest
            <label>
                <span>{{ __('capell-comments::generic.name') }}</span>
                <input
                    type="text"
                    wire:model="authorName"
                    autocomplete="name"
                    aria-describedby="comments-author-name-error"
                />
            </label>
            @error('authorName')
                <p
                    id="comments-author-name-error"
                    role="alert"
                >
                    {{ $message }}
                </p>
            @enderror

            <label>
                <span>{{ __('capell-comments::generic.email') }}</span>
                <input
                    type="email"
                    wire:model="authorEmail"
                    autocomplete="email"
                    aria-describedby="comments-author-email-error"
                />
            </label>
            @error('authorEmail')
                <p
                    id="comments-author-email-error"
                    role="alert"
                >
                    {{ $message }}
                </p>
            @enderror
        @endguest

        <label>
            <span>{{ __('capell-comments::generic.comment') }}</span>
            <textarea
                wire:model="body"
                rows="4"
                aria-describedby="comments-body-error"
            ></textarea>
        </label>
        @error('body')
            <p
                id="comments-body-error"
                role="alert"
            >
                {{ $message }}
            </p>
        @enderror

        @error('parent')
            <p role="alert">{{ $message }}</p>
        @enderror

        @error('commentable')
            <p role="alert">{{ $message }}</p>
        @enderror

        @error('author')
            <p role="alert">{{ $message }}</p>
        @enderror

        <button
            type="submit"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove>
                {{ __('capell-comments::generic.submit') }}
            </span>
            <span wire:loading>
                {{ __('capell-comments::generic.submitting') }}
            </span>
        </button>
    </form>
</section>
