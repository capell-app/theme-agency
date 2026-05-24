<div
    data-comments-thread
    data-thread-key="{{ $threadKey }}"
    data-thread-url="{{ route('capell-comments.thread') }}"
></div>
<script>
    document.querySelectorAll('[data-comments-thread]').forEach((element) => {
        if (element.dataset.loaded === 'true') {
            return
        }

        element.dataset.loaded = 'true'
        const url = new URL(element.dataset.threadUrl, window.location.origin)
        url.searchParams.set('thread', element.dataset.threadKey)

        fetch(url, {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => (response.ok ? response.text() : ''))
            .then((html) => {
                if (html !== '') {
                    element.innerHTML = html
                }
            })
    })
</script>
