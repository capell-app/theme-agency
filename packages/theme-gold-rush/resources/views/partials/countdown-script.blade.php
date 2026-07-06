{{--
    Shared countdown tick script (Wave 2 §0.2 — live-state policy): reads the
    server-rendered `data-deadline` target on the immediately preceding
    element and ticks the clock client-side only. Nothing here polls or
    pushes; "closed" is only ever an editorial payload state decided at
    render time by the calling view, never detected by this script.
--}}
<script>
    ;(function () {
        var countdownElement = document.currentScript.previousElementSibling
        if (!countdownElement) {
            return
        }
        var target = countdownElement.hasAttribute('data-deadline')
            ? countdownElement
            : countdownElement.querySelector('[data-deadline]')
        var valueElement = countdownElement.querySelector(
            '[data-deadline-value]',
        )
        if (!target || !valueElement) {
            return
        }
        var targetTime = Date.parse(target.getAttribute('data-deadline'))
        if (isNaN(targetTime)) {
            return
        }

        function tick() {
            var remainingMs = targetTime - Date.now()
            if (remainingMs <= 0) {
                valueElement.textContent = '00:00:00'
                clearInterval(intervalId)
                return
            }
            var totalSeconds = Math.floor(remainingMs / 1000)
            var hours = Math.floor(totalSeconds / 3600)
            var minutes = Math.floor((totalSeconds % 3600) / 60)
            var seconds = totalSeconds % 60
            var pad = function (unit) {
                return String(unit).padStart(2, '0')
            }
            valueElement.textContent =
                pad(hours) + ':' + pad(minutes) + ':' + pad(seconds)
        }

        tick()
        var intervalId = setInterval(tick, 1000)
    })()
</script>
