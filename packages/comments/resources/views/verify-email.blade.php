<main>
    @if (session('capell_comments_verified') === true)
        <p>{{ __('capell-comments::messages.email_verified') }}</p>
    @else
        <form
            method="POST"
            action="{{ route('capell-comments.verify.store', ['token' => $token]) }}"
        >
            @csrf
            <p>
                {{ __('capell-comments::messages.verify_email_confirmation') }}
            </p>
            <button type="submit">
                {{ __('capell-comments::messages.verify_email_action') }}
            </button>
        </form>
    @endif
</main>
