;(function () {
    'use strict'

    var configElement = document.querySelector('[data-campaign-tracker]')

    if (!configElement) {
        return
    }

    var config = {}

    try {
        config = JSON.parse(configElement.textContent || '{}')
    } catch (error) {
        return
    }

    if (!config.conversionsUrl) {
        return
    }

    var visitStorageKey = 'capell_insights_visit_id'
    var visitCookieName = 'capell_insights_visit'
    var pageViewTracked = false

    function currentVisitId() {
        var storedVisitId = null

        try {
            storedVisitId = window.localStorage.getItem(visitStorageKey)
        } catch (error) {
            storedVisitId = null
        }

        return storedVisitId || currentVisitCookie()
    }

    function currentVisitCookie() {
        var cookiePrefix = visitCookieName + '='
        var cookies = document.cookie ? document.cookie.split(';') : []
        var matchingCookie = cookies.find(function (cookie) {
            return cookie.trim().indexOf(cookiePrefix) === 0
        })

        if (!matchingCookie) {
            return null
        }

        return decodeURIComponent(
            matchingCookie.trim().slice(cookiePrefix.length),
        )
    }

    function sendConversion(payload) {
        var body = JSON.stringify(
            Object.assign(
                {
                    url: window.location.href,
                    visit_id: currentVisitId(),
                },
                payload,
            ),
        )

        if (navigator.sendBeacon) {
            var blob = new Blob([body], { type: 'application/json' })

            if (navigator.sendBeacon(config.conversionsUrl, blob)) {
                return
            }
        }

        fetch(config.conversionsUrl, {
            method: 'POST',
            body: body,
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
            keepalive: true,
        }).catch(function () {})
    }

    function trackPageView() {
        if (pageViewTracked) {
            return
        }

        pageViewTracked = true

        sendConversion({ type: 'page_view' })
    }

    function trackClick(event) {
        var target =
            event.target && event.target.closest
                ? event.target.closest(
                      'a[data-campaign-goal], button[data-campaign-goal], [role="button"][data-campaign-goal]',
                  )
                : null

        if (!target) {
            return
        }

        var goalKey = target.getAttribute('data-campaign-goal')

        if (!goalKey) {
            return
        }

        sendConversion({
            type: 'cta_click',
            goal_key: goalKey,
            cta_key: target.getAttribute('data-campaign-cta'),
        })
    }

    document.addEventListener('click', trackClick, true)

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', trackPageView, {
            once: true,
        })
    } else {
        trackPageView()
    }
})()
