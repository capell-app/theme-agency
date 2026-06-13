;(function () {
    if (window.CapellLiveChatLoaded) {
        return
    }
    window.CapellLiveChatLoaded = true
    var embeddedConfig = @json($config?->toArray(), JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG);
    var style = document.createElement('style')
    style.textContent = [
        '.capell-live-chat{position:fixed;right:18px;bottom:18px;z-index:2147483000;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:var(--clc-text,#101715)}',
        '.capell-live-chat *{box-sizing:border-box}',
        '.capell-live-chat button,.capell-live-chat input,.capell-live-chat textarea{font:inherit}',
        '.capell-live-chat__launcher{display:inline-flex;align-items:center;gap:10px;border:0;border-radius:10px;background:var(--clc-primary,#087765);color:#f8fffb;padding:12px 14px;box-shadow:0 16px 40px rgba(16,23,21,.18);cursor:pointer}',
        '.capell-live-chat__avatar{display:grid;width:30px;height:30px;place-items:center;border-radius:8px;background:rgba(255,255,255,.18);font-weight:700}',
        '.capell-live-chat__panel{display:none;width:min(390px,calc(100vw - 24px));height:min(640px,calc(100vh - 34px));overflow:hidden;border:1px solid #cfd9d3;border-radius:12px;background:var(--clc-surface,#fcfffb);box-shadow:0 22px 70px rgba(16,23,21,.24)}',
        '.capell-live-chat.is-open .capell-live-chat__panel{display:flex;flex-direction:column}',
        '.capell-live-chat.is-open .capell-live-chat__launcher{display:none}',
        '.capell-live-chat__header{display:flex;align-items:center;justify-content:space-between;gap:12px;background:#0b1716;color:#f8fffb;padding:14px}',
        '.capell-live-chat__identity{display:flex;align-items:center;gap:10px;min-width:0}',
        '.capell-live-chat__title{font-weight:700;line-height:1.2}',
        '.capell-live-chat__subtitle{font-size:12px;color:#cfe2dc;line-height:1.35}',
        '.capell-live-chat__icon-button{display:grid;width:34px;height:34px;place-items:center;border:1px solid rgba(255,255,255,.2);border-radius:8px;background:transparent;color:#f8fffb;cursor:pointer}',
        '.capell-live-chat__messages{display:flex;flex:1;flex-direction:column;gap:10px;overflow:auto;padding:14px;background:#f5f7f4}',
        '.capell-live-chat__message{max-width:86%;border-radius:10px;padding:10px 11px;font-size:14px;line-height:1.45;white-space:pre-wrap}',
        '.capell-live-chat__message--assistant,.capell-live-chat__message--system{align-self:flex-start;background:#fff;border:1px solid #dce5df;color:#101715}',
        '.capell-live-chat__message--visitor{align-self:flex-end;background:var(--clc-primary,#087765);color:#f8fffb}',
        '.capell-live-chat__tabs{display:flex;gap:6px;padding:10px 12px 0;background:#fcfffb}',
        '.capell-live-chat__tab{flex:1;border:1px solid #cfd9d3;border-radius:8px;background:#fff;color:#244c43;padding:8px;cursor:pointer;font-size:13px}',
        '.capell-live-chat__tab[aria-selected=true]{border-color:var(--clc-primary,#087765);background:#e7efeb;color:#101715;font-weight:700}',
        '.capell-live-chat__form{display:flex;flex-direction:column;gap:8px;border-top:1px solid #cfd9d3;padding:12px;background:#fcfffb}',
        '.capell-live-chat__details{display:none;grid-template-columns:1fr 1fr;gap:8px}',
        '.capell-live-chat.show-details .capell-live-chat__details{display:grid}',
        '.capell-live-chat__field{width:100%;border:1px solid #cfd9d3;border-radius:8px;background:#fff;color:#101715;padding:9px 10px;outline:0}',
        '.capell-live-chat__field:focus{border-color:var(--clc-primary,#087765);box-shadow:0 0 0 3px rgba(8,119,101,.16)}',
        '.capell-live-chat__textarea{min-height:72px;resize:vertical}',
        '.capell-live-chat__controls{display:flex;align-items:center;justify-content:space-between;gap:8px}',
        '.capell-live-chat__file{max-width:150px;font-size:12px;color:#52615b}',
        '.capell-live-chat__actions{display:flex;gap:8px}',
        '.capell-live-chat__secondary,.capell-live-chat__send{border-radius:8px;padding:9px 11px;cursor:pointer}',
        '.capell-live-chat__secondary{border:1px solid #cfd9d3;background:#fff;color:#244c43}',
        '.capell-live-chat__send{border:1px solid var(--clc-primary,#087765);background:var(--clc-primary,#087765);color:#f8fffb;font-weight:700}',
        '.capell-live-chat__send:disabled{opacity:.6;cursor:not-allowed}',
        '.capell-live-chat__status{min-height:18px;font-size:12px;color:#52615b}',
        '@media (max-width:480px){.capell-live-chat{right:12px;bottom:12px}.capell-live-chat__panel{width:calc(100vw - 24px);height:calc(100vh - 24px)}.capell-live-chat__details{grid-template-columns:1fr}.capell-live-chat__controls{align-items:stretch;flex-direction:column}.capell-live-chat__actions{width:100%}.capell-live-chat__secondary,.capell-live-chat__send{flex:1}}',
    ].join('')
    document.head.appendChild(style)
    if (
        embeddedConfig &&
        !document.querySelector('[data-capell-live-chat-widget]')
    ) {
        var embeddedRoot = document.createElement('div')
        embeddedRoot.setAttribute('data-capell-live-chat-widget', '')
        embeddedRoot.dataset.config = JSON.stringify(embeddedConfig)
        document.body.appendChild(embeddedRoot)
    }
    document
        .querySelectorAll('[data-capell-live-chat-widget]')
        .forEach(function (root) {
            if (root.dataset.initialized === 'true') {
                return
            }
            root.dataset.initialized = 'true'
            initWidget(root)
        })
    function initWidget(root) {
        var config = normalizeConfig(parseConfig(root.dataset.config))
        var storageKey = 'capell_live_chat_' + (config.brandName || 'support')
        var stored = readStorage(storageKey)
        var state = {
            open: false,
            mode: 'message_first',
            sending: false,
            visitorToken: stored.visitorToken || randomToken(),
            conversationUuid: stored.conversationUuid || null,
            messages: Array.isArray(stored.messages) ? stored.messages : [],
        }
        root.className = 'capell-live-chat'
        root.style.setProperty(
            '--clc-primary',
            config.branding && config.branding.primary
                ? config.branding.primary
                : '#087765',
        )
        root.style.setProperty(
            '--clc-surface',
            config.branding && config.branding.surface
                ? config.branding.surface
                : '#fcfffb',
        )
        root.style.setProperty(
            '--clc-text',
            config.branding && config.branding.text
                ? config.branding.text
                : '#101715',
        )
        root.innerHTML = buildMarkup(config)
        var launcher = root.querySelector('[data-clc-launcher]')
        var closeButton = root.querySelector('[data-clc-close]')
        var messages = root.querySelector('[data-clc-messages]')
        var form = root.querySelector('[data-clc-form]')
        var status = root.querySelector('[data-clc-status]')
        var textarea = root.querySelector('[data-clc-body]')
        var handoffButton = root.querySelector('[data-clc-handoff]')
        var tabs = root.querySelectorAll('[data-clc-mode]')
        if (state.messages.length === 0) {
            state.messages.push({
                role: 'assistant',
                body: config.welcomeMessage,
            })
            state.messages.push({ role: 'system', body: config.aiDisclosure })
        }
        renderMessages(messages, state.messages)
        persist(storageKey, state)
        scheduleProactivePrompt(config, state, messages, storageKey)
        launcher.addEventListener('click', function () {
            state.open = true
            root.classList.add('is-open')
            textarea.focus()
        })
        closeButton.addEventListener('click', function () {
            state.open = false
            root.classList.remove('is-open')
        })
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                state.mode = tab.dataset.clcMode || 'message_first'
                tabs.forEach(function (candidate) {
                    candidate.setAttribute(
                        'aria-selected',
                        candidate === tab ? 'true' : 'false',
                    )
                })
                root.classList.toggle(
                    'show-details',
                    state.mode === 'details_first',
                )
            })
        })
        form.addEventListener('submit', function (event) {
            event.preventDefault()
            submitMessage(config, state, form, status, messages, storageKey)
        })
        handoffButton.addEventListener('click', function () {
            requestHandoff(config, state, form, status, messages, storageKey)
        })
    }
    function buildMarkup(config) {
        return (
            '' +
            ' <button     type="button"     class="capell-live-chat__launcher"     data-clc-launcher     aria-label="' +
            escapeHtml(config.brandName) +
            '" >     ' +
            '     <span class="capell-live-chat__avatar">         ' +
            escapeHtml(config.avatarInitials) +
            '     </span>     ' +
            '     <span>' +
            escapeHtml(config.brandName) +
            '</span>     ' +
            ' </button> ' +
            ' <section     class="capell-live-chat__panel"     aria-live="polite"     aria-label="' +
            escapeHtml(config.brandName) +
            '" >     ' +
            '     <header class="capell-live-chat__header">         ' +
            '         <div class="capell-live-chat__identity">             ' +
            '             <span class="capell-live-chat__avatar">                 ' +
            escapeHtml(config.avatarInitials) +
            '             </span>             ' +
            '             <div>                 <div class="capell-live-chat__title">                     ' +
            escapeHtml(config.agentName) +
            '                 </div>                 <div class="capell-live-chat__subtitle">                     ' +
            escapeHtml(config.aiDisclosure) +
            '                 </div>             </div>             ' +
            '         </div>         ' +
            '         <button             type="button"             class="capell-live-chat__icon-button"             data-clc-close             aria-label="' +
            escapeHtml(config.labels.close) +
            '"         >             x         </button>         ' +
            '     </header>     ' +
            '     <div         class="capell-live-chat__messages"         data-clc-messages     ></div>     ' +
            '     <div         class="capell-live-chat__tabs"         role="tablist"     >         ' +
            '         <button             type="button"             class="capell-live-chat__tab"             data-clc-mode="message_first"             aria-selected="true"         >             ' +
            escapeHtml(config.messageFirstLabel) +
            '         </button>         ' +
            '         <button             type="button"             class="capell-live-chat__tab"             data-clc-mode="details_first"             aria-selected="false"         >             ' +
            escapeHtml(config.detailsFirstLabel) +
            '         </button>         ' +
            '     </div>     ' +
            '     <form         class="capell-live-chat__form"         data-clc-form         enctype="multipart/form-data"     >         ' +
            '         <div class="capell-live-chat__details">             ' +
            '             <input                 class="capell-live-chat__field"                 name="name"                 autocomplete="name"                 placeholder="' +
            escapeHtml(config.labels.name) +
            '"             />             ' +
            '             <input                 class="capell-live-chat__field"                 name="email"                 type="email"                 autocomplete="email"                 placeholder="' +
            escapeHtml(config.labels.email) +
            '"             />             ' +
            '             <input                 class="capell-live-chat__field"                 name="phone"                 autocomplete="tel"                 placeholder="' +
            escapeHtml(config.labels.phone) +
            '"             />             ' +
            '             <input                 class="capell-live-chat__field"                 name="company"                 autocomplete="organization"                 placeholder="' +
            escapeHtml(config.labels.company) +
            '"             />             ' +
            '         </div>         ' +
            '         <textarea             class="capell-live-chat__field capell-live-chat__textarea"             data-clc-body             name="body"             required             placeholder="' +
            escapeHtml(config.messagePlaceholder) +
            '"         ></textarea>         ' +
            '         <div class="capell-live-chat__controls">             ' +
            '             <input                 class="capell-live-chat__file"                 name="attachments[]"                 type="file"                 multiple             />             ' +
            '             <div class="capell-live-chat__actions">                 ' +
            '                 <button                     type="button"                     class="capell-live-chat__secondary"                     data-clc-handoff                 >                     ' +
            escapeHtml(config.handoffLabel) +
            '                 </button>                 ' +
            '                 <button                     type="submit"                     class="capell-live-chat__send"                 >                     ' +
            escapeHtml(config.labels.send) +
            '                 </button>                 ' +
            '             </div>             ' +
            '         </div>         ' +
            '         <div             class="capell-live-chat__status"             data-clc-status         ></div>         ' +
            '     </form>     ' +
            ' </section> '
        )
    }
    function submitMessage(config, state, form, status, messages, storageKey) {
        if (state.sending) {
            return
        }
        state.sending = true
        status.textContent = config.labels.sending
        var body = form.querySelector('[name="body"]').value || ''
        var payload = formPayload(form, state, body)
        var url = state.conversationUuid
            ? config.messageUrl.replace(
                  '{conversation}',
                  encodeURIComponent(state.conversationUuid),
              )
            : config.startUrl
        fetch(url, {
            method: 'POST',
            body: payload,
            headers: { Accept: 'application/json' },
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Request failed')
                }
                return response.json()
            })
            .then(function (json) {
                if (json.conversation && json.conversation.uuid) {
                    state.conversationUuid = json.conversation.uuid
                }
                if (Array.isArray(json.messages)) {
                    json.messages.forEach(function (message) {
                        state.messages.push({
                            role: message.role,
                            body: message.body,
                        })
                    })
                }
                if (json.conversation && json.conversation.requires_contact) {
                    form.closest('.capell-live-chat').classList.add(
                        'show-details',
                    )
                }
                form.reset()
                status.textContent = ''
                renderMessages(messages, state.messages)
                persist(storageKey, state)
            })
            .catch(function () {
                status.textContent = config.labels.requestFailed
            })
            .finally(function () {
                state.sending = false
            })
    }
    function requestHandoff(config, state, form, status, messages, storageKey) {
        if (!state.conversationUuid) {
            form.closest('.capell-live-chat').classList.add('show-details')
            status.textContent = config.labels.handoffRequiresConversation
            return
        }
        var payload = new FormData()
        payload.append('visitor_token', state.visitorToken)
        addVisitor(payload, form)
        fetch(
            config.handoffUrl.replace(
                '{conversation}',
                encodeURIComponent(state.conversationUuid),
            ),
            {
                method: 'POST',
                body: payload,
                headers: { Accept: 'application/json' },
            },
        )
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Request failed')
                }
                return response.json()
            })
            .then(function (json) {
                state.messages.push({
                    role: 'system',
                    body: json.message || config.labels.handoffRequested,
                })
                status.textContent = ''
                renderMessages(messages, state.messages)
                persist(storageKey, state)
            })
            .catch(function () {
                status.textContent = config.labels.handoffFailed
            })
    }
    function formPayload(form, state, body) {
        var payload = new FormData()
        payload.append('body', body)
        payload.append('visitor_token', state.visitorToken)
        payload.append('flow', state.mode)
        payload.append(
            'timezone',
            Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC',
        )
        payload.append(
            'locale',
            document.documentElement.lang || navigator.language || 'en',
        )
        payload.append('page[url]', window.location.href)
        payload.append('page[referrer]', document.referrer || '')
        addVisitor(payload, form)
        var fileInput = form.querySelector('[name="attachments[]"]')
        if (fileInput && fileInput.files) {
            Array.prototype.forEach.call(fileInput.files, function (file) {
                payload.append('attachments[]', file)
            })
        }
        return payload
    }
    function addVisitor(payload, form) {
        ;['name', 'email', 'phone', 'company'].forEach(function (field) {
            var input = form.querySelector('[name="' + field + '"]')
            if (input && input.value) {
                payload.append('visitor[' + field + ']', input.value)
            }
        })
        payload.append('visitor[processing_consent]', '1')
    }
    function renderMessages(container, messages) {
        container.innerHTML = ''
        messages.forEach(function (message) {
            var bubble = document.createElement('div')
            var role = message.role || 'assistant'
            bubble.className =
                'capell-live-chat__message capell-live-chat__message--' + role
            bubble.textContent = message.body || ''
            container.appendChild(bubble)
        })
        container.scrollTop = container.scrollHeight
    }
    function scheduleProactivePrompt(config, state, messages, storageKey) {
        if (
            !Array.isArray(config.proactiveTriggers) ||
            state.messages.length > 2
        ) {
            return
        }
        config.proactiveTriggers.forEach(function (trigger) {
            if (!pathMatches(trigger.path || '*')) {
                return
            }
            window.setTimeout(
                function () {
                    if (state.messages.length > 2) {
                        return
                    }
                    state.messages.push({
                        role: 'assistant',
                        body: trigger.message,
                    })
                    renderMessages(messages, state.messages)
                    persist(storageKey, state)
                },
                Math.max(1, Number(trigger.delay_seconds || 8)) * 1000,
            )
        })
    }
    function pathMatches(pattern) {
        var expression =
            '^' +
            String(pattern)
                .replace(/[.+?^${}()|[\]\\]/g, '\\$&')
                .replace(/\*/g, '.*') +
            '$'
        return new RegExp(expression).test(window.location.pathname)
    }
    function readStorage(key) {
        try {
            return JSON.parse(window.localStorage.getItem(key) || '{}') || {}
        } catch (error) {
            return {}
        }
    }
    function persist(key, state) {
        window.localStorage.setItem(
            key,
            JSON.stringify({
                visitorToken: state.visitorToken,
                conversationUuid: state.conversationUuid,
                messages: state.messages.slice(-60),
            }),
        )
    }
    function parseConfig(value) {
        try {
            return JSON.parse(value || '{}')
        } catch (error) {
            return {}
        }
    }
    function normalizeConfig(raw) {
        var labels = readValue(raw, 'labels', 'labels', {})
        return {
            agentName: readValue(raw, 'agentName', 'agent_name', 'Lara'),
            avatarInitials: readValue(
                raw,
                'avatarInitials',
                'avatar_initials',
                'LA',
            ),
            brandName: readValue(raw, 'brandName', 'brand_name', 'Support'),
            welcomeMessage: readValue(
                raw,
                'welcomeMessage',
                'welcome_message',
                'Hi, I am Lara, the AI assistant. Ask a question or leave your details and we will help.',
            ),
            aiDisclosure: readValue(
                raw,
                'aiDisclosure',
                'ai_disclosure',
                'I am an AI assistant. I can answer from approved content and bring in a person when needed.',
            ),
            messagePlaceholder: readValue(
                raw,
                'messagePlaceholder',
                'message_placeholder',
                'Type your message',
            ),
            messageFirstLabel: readValue(
                raw,
                'messageFirstLabel',
                'message_first_label',
                'Ask first',
            ),
            detailsFirstLabel: readValue(
                raw,
                'detailsFirstLabel',
                'details_first_label',
                'Leave details first',
            ),
            handoffLabel: readValue(
                raw,
                'handoffLabel',
                'handoff_label',
                'Ask a person',
            ),
            startUrl: readValue(raw, 'startUrl', 'start_url', ''),
            messageUrl: readValue(raw, 'messageUrl', 'message_url', ''),
            handoffUrl: readValue(raw, 'handoffUrl', 'handoff_url', ''),
            branding: readValue(raw, 'branding', 'branding', {}),
            proactiveTriggers: readValue(
                raw,
                'proactiveTriggers',
                'proactive_triggers',
                [],
            ),
            labels: {
                close: readValue(labels, 'close', 'close', 'Close'),
                name: readValue(labels, 'name', 'name', 'Name'),
                email: readValue(labels, 'email', 'email', 'Email'),
                phone: readValue(labels, 'phone', 'phone', 'Phone'),
                company: readValue(labels, 'company', 'company', 'Company'),
                send: readValue(labels, 'send', 'send', 'Send'),
                sending: readValue(labels, 'sending', 'sending', 'Sending...'),
                requestFailed: readValue(
                    labels,
                    'requestFailed',
                    'request_failed',
                    'Message could not be sent. Please try again.',
                ),
                handoffRequiresConversation: readValue(
                    labels,
                    'handoffRequiresConversation',
                    'handoff_requires_conversation',
                    'Send a message first so we can include the chat history.',
                ),
                handoffRequested: readValue(
                    labels,
                    'handoffRequested',
                    'handoff_requested',
                    'Human handoff requested.',
                ),
                handoffFailed: readValue(
                    labels,
                    'handoffFailed',
                    'handoff_failed',
                    'Handoff could not be requested. Please try again.',
                ),
            },
        }
    }
    function readValue(source, camelKey, snakeKey, fallback) {
        if (!source || typeof source !== 'object') {
            return fallback
        }
        if (
            source[camelKey] !== undefined &&
            source[camelKey] !== null &&
            source[camelKey] !== ''
        ) {
            return source[camelKey]
        }
        if (
            source[snakeKey] !== undefined &&
            source[snakeKey] !== null &&
            source[snakeKey] !== ''
        ) {
            return source[snakeKey]
        }
        return fallback
    }
    function randomToken() {
        if (window.crypto && window.crypto.randomUUID) {
            return window.crypto.randomUUID()
        }
        return String(Date.now()) + String(Math.random()).slice(2)
    }
    function escapeHtml(value) {
        return String(value || '')
            .split('&')
            .join('&amp;')
            .split('<')
            .join('&lt;')
            .split('>')
            .join('&gt;')
            .split('"')
            .join('&quot;')
            .split("'")
            .join('&#039;')
    }
})()
