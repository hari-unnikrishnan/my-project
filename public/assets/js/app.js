document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const status = document.querySelector('.preview-status') || document.createElement('div');

    status.classList.add('preview-status');
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');
    if (!status.isConnected) document.body.appendChild(status);

    const notify = (message) => {
        status.textContent = message;
        status.classList.add('show');
        window.clearTimeout(notify.timeout);
        notify.timeout = window.setTimeout(() => status.classList.remove('show'), 3200);
    };

    const applyTheme = (theme) => {
        body.classList.toggle('theme-light', theme === 'light');
        body.classList.toggle('theme-dark', theme !== 'light');
        if (themeToggle) {
            themeToggle.innerHTML = theme === 'light'
                ? '<i class="bi bi-moon-stars-fill"></i>'
                : '<i class="bi bi-sun-fill"></i>';
            themeToggle.setAttribute('aria-label', theme === 'light' ? 'Switch to dark mode' : 'Switch to light mode');
        }
    };

    applyTheme(body.dataset.theme || localStorage.getItem('aether-theme') || (body.classList.contains('theme-light') ? 'light' : 'dark'));

    const saveTheme = async (theme) => {
        const previousTheme = body.classList.contains('theme-light') ? 'light' : 'dark';
        applyTheme(theme);

        if (!body.dataset.themeEndpoint) {
            localStorage.setItem('aether-theme', theme);
            return;
        }

        try {
            const response = await fetch(body.dataset.themeEndpoint, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ theme }),
            });
            if (!response.ok) throw new Error('Theme preference save failed.');
            body.dataset.theme = theme;
        } catch {
            applyTheme(previousTheme);
            notify('Your theme change could not be saved. Please try again.');
        }
    };

    if (sidebarToggle && sidebar) {
        const backdrop = document.createElement('button');
        backdrop.className = 'sidebar-backdrop';
        backdrop.type = 'button';
        backdrop.setAttribute('aria-label', 'Close navigation');
        document.body.appendChild(backdrop);

        sidebarToggle.addEventListener('click', () => {
            if (window.matchMedia('(max-width: 860px)').matches) {
                body.classList.toggle('sidebar-open');
                sidebarToggle.setAttribute('aria-expanded', String(body.classList.contains('sidebar-open')));
                return;
            }

            sidebar.classList.toggle('collapsed');
            sidebarToggle.setAttribute('aria-expanded', String(!sidebar.classList.contains('collapsed')));
        });

        backdrop.addEventListener('click', () => {
            body.classList.remove('sidebar-open');
            sidebarToggle.setAttribute('aria-expanded', 'false');
        });

        sidebar.querySelectorAll('.nav-item').forEach((link) => {
            link.addEventListener('click', () => {
                body.classList.remove('sidebar-open');
                sidebarToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const theme = body.classList.contains('theme-light') ? 'dark' : 'light';
            saveTheme(theme);
        });
    }

    document.querySelectorAll('[data-theme-choice]').forEach((choice) => {
        choice.addEventListener('click', () => {
            const theme = choice.dataset.themeChoice;
            if (theme !== 'light' && theme !== 'dark') return;
            saveTheme(theme);
            document.querySelectorAll('[data-theme-choice]').forEach((item) => {
                item.classList.toggle('active', item === choice);
            });
        });
    });

    document.querySelectorAll('[data-suggest]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.querySelector('[data-chat-input]');
            if (!input) return;
            input.value = button.dataset.suggest || button.textContent.trim();
            input.focus();
        });
    });

    const chatForm = document.querySelector('[data-chat-form]');
    const chatMessages = document.querySelector('[data-chat-messages]');
    const chatError = document.querySelector('[data-chat-error]');

    if (chatForm && chatMessages) {
        const chatInput = chatForm.querySelector('[data-chat-input]');
        const sendButton = chatForm.querySelector('button[type="submit"]');
        let isSending = false;

        const scrollChatToLatest = () => {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        };

        const createMessage = (message, userInitial) => {
            const article = document.createElement('article');
            article.className = `message ${message.role === 'user' ? 'message-user' : 'message-assistant'}`;

            const avatar = document.createElement('div');
            avatar.className = `message-avatar ${message.role === 'user' ? 'message-avatar--user' : 'message-avatar--assistant'}`;
            avatar.textContent = message.role === 'user' ? userInitial : 'A';

            const bubble = document.createElement('div');
            bubble.className = `message-bubble ${message.role === 'assistant' ? 'message-bubble--assistant' : ''}`;

            const content = document.createElement('p');
            content.className = 'message-content';
            content.textContent = message.content;
            bubble.appendChild(content);

            if (message.role === 'assistant') {
                const actions = document.createElement('div');
                actions.className = 'message-actions';

                const copyButton = document.createElement('button');
                copyButton.className = 'mini-action';
                copyButton.type = 'button';
                copyButton.dataset.copyText = '';
                copyButton.setAttribute('aria-label', 'Copy response');
                copyButton.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
                actions.appendChild(copyButton);

                if (message.regenerate_url) {
                    const regenerateForm = document.createElement('form');
                    regenerateForm.method = 'POST';
                    regenerateForm.action = message.regenerate_url;

                    const token = document.createElement('input');
                    token.type = 'hidden';
                    token.name = '_token';
                    token.value = document.querySelector('meta[name="csrf-token"]')?.content || '';

                    const regenerateButton = document.createElement('button');
                    regenerateButton.className = 'mini-action';
                    regenerateButton.type = 'submit';
                    regenerateButton.setAttribute('aria-label', 'Regenerate response');
                    regenerateButton.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Regenerate';
                    regenerateForm.append(token, regenerateButton);
                    actions.appendChild(regenerateForm);
                }

                bubble.appendChild(actions);
            }

            article.append(avatar, bubble);
            return article;
        };

        const createTypingIndicator = () => {
            const article = document.createElement('article');
            article.className = 'message message-assistant';
            article.dataset.chatPending = '';

            const avatar = document.createElement('div');
            avatar.className = 'message-avatar message-avatar--assistant';
            avatar.textContent = 'A';

            const bubble = document.createElement('div');
            bubble.className = 'message-bubble message-bubble--assistant';

            const dots = document.createElement('div');
            dots.className = 'typing-skeleton';
            dots.setAttribute('role', 'status');
            dots.setAttribute('aria-label', 'Aether AI is responding');
            for (let index = 0; index < 3; index += 1) dots.appendChild(document.createElement('span'));

            bubble.appendChild(dots);
            article.append(avatar, bubble);
            return article;
        };

        const showChatError = (message) => {
            if (!chatError) {
                notify(message);
                return;
            }

            chatError.textContent = message;
            chatError.hidden = false;
        };

        chatForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (isSending || !chatInput) return;

            const messageText = chatInput.value.trim();
            if (!messageText) {
                chatInput.focus();
                return;
            }

            const formData = new FormData(chatForm);
            formData.set('message', messageText);
            isSending = true;
            chatError?.setAttribute('hidden', '');
            chatError?.replaceChildren();
            chatForm.setAttribute('aria-busy', 'true');
            sendButton.disabled = true;
            sendButton.setAttribute('aria-disabled', 'true');
            chatInput.disabled = true;
            const originalButtonContent = sendButton.innerHTML;
            sendButton.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Sending';

            const welcome = chatMessages.querySelector('.chat-welcome');
            welcome?.remove();

            const userMessage = createMessage({
                role: 'user',
                content: messageText,
            }, chatForm.dataset.userInitial || 'U');
            const pendingMessage = createTypingIndicator();
            chatMessages.append(userMessage, pendingMessage);
            scrollChatToLatest();

            try {
                const response = await fetch(chatForm.action, {
                    method: chatForm.method || 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: formData,
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message || payload.errors?.message?.[0] || 'Your message could not be sent. Please try again.');
                }

                if (!Array.isArray(payload.messages) || payload.messages.length === 0) {
                    throw new Error('The AI service returned an unexpected response. Please try again.');
                }

                userMessage.remove();
                pendingMessage.remove();
                payload.messages.forEach((message) => {
                    if (typeof message.content === 'string' && (message.role === 'user' || message.role === 'assistant')) {
                        chatMessages.appendChild(createMessage(message, chatForm.dataset.userInitial || 'U'));
                    }
                });
                chatInput.value = '';

                if (payload.message_url) chatForm.action = payload.message_url;
                if (payload.redirect_url) {
                    window.history.pushState({}, '', payload.redirect_url);
                    const heading = document.querySelector('[data-chat-heading]');
                    if (heading && payload.title) heading.textContent = payload.title;
                    const context = document.querySelector('[data-chat-context]');
                    if (context) context.textContent = 'Saved conversation';

                    let conversationList = document.querySelector('.conversation-list');
                    if (!conversationList) {
                        document.querySelector('.compact-empty')?.remove();
                        conversationList = document.createElement('div');
                        conversationList.className = 'conversation-list';
                        document.querySelector('.suggestion-box')?.before(conversationList);
                    }
                    if (!conversationList.querySelector(`a[href="${CSS.escape(payload.redirect_url)}"]`)) {
                        const link = document.createElement('a');
                        link.className = 'context-card active';
                        link.href = payload.redirect_url;
                        const topline = document.createElement('div');
                        topline.className = 'context-topline';
                        const title = document.createElement('strong');
                        title.textContent = payload.title || messageText;
                        const count = document.createElement('span');
                        count.className = 'status-pill neutral';
                        count.textContent = String(payload.messages.length);
                        const time = document.createElement('small');
                        time.textContent = 'just now';
                        topline.append(title, count);
                        link.append(topline, time);
                        conversationList.prepend(link);
                    }
                }

                scrollChatToLatest();
            } catch (error) {
                userMessage.remove();
                pendingMessage.remove();
                showChatError(error instanceof Error ? error.message : 'Your message could not be sent. Please try again.');
            } finally {
                isSending = false;
                chatForm.removeAttribute('aria-busy');
                sendButton.disabled = false;
                sendButton.removeAttribute('aria-disabled');
                chatInput.disabled = false;
                sendButton.innerHTML = originalButtonContent;
                chatInput.focus();
            }
        });

        chatInput?.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' || event.shiftKey || event.isComposing) return;
            event.preventDefault();
            if (!isSending) chatForm.requestSubmit();
        });

        scrollChatToLatest();
    }

    document.querySelectorAll('[data-open-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            const modal = document.querySelector(button.dataset.openModal || '[data-modal]');
            if (!modal) return;
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', () => {
            const modal = button.closest('[data-modal]');
            if (!modal) return;
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        });
    });

    document.querySelectorAll('[data-modal]').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target !== modal) return;
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        });
    });

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-copy-output], [data-copy-code], [data-copy-text]');
        if (!button) return;

        const source = button.closest('.code-block')?.querySelector('pre')
            || button.closest('.message')?.querySelector('.message-content')
            || button.closest('.tool-output')?.querySelector('[data-output], .output-content');
        const text = source?.innerText || source?.textContent;
        if (!text) {
            notify('There is no generated content to copy yet.');
            return;
        }

        try {
            await navigator.clipboard.writeText(text);
            notify('Copied to clipboard.');
        } catch {
            notify('Clipboard access is unavailable in this browser.');
        }
    });

    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });
});
