export class InputHandler {
    constructor(chatManager) {
        this.chatManager = chatManager;
        this.textarea = document.querySelector('#text-request textarea');
        this.sendButton = document.querySelector('#button-send button');
        this.modelSelect = document.querySelector('select[name="model"]');

        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        if (this.textarea) {
            this.textarea.addEventListener('input', () => this.autoResize());
            this.textarea.addEventListener('keydown', (e) => this.handleKeyDown(e));
        }

        if (this.sendButton) {
            this.sendButton.addEventListener('click', () => this.handleSendMessage());
        }
    }

    autoResize() {
        if (!this.textarea) return;
        this.textarea.style.height = 'auto';
        const maxHeight = 200;
        const newHeight = Math.min(this.textarea.scrollHeight, maxHeight);
        this.textarea.style.height = newHeight + 'px';
        this.textarea.style.overflowY = newHeight >= maxHeight ? 'auto' : 'hidden';
    }

    handleKeyDown(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            this.handleSendMessage();
        }
    }

    async handleSendMessage() {
        if (!this.textarea) return;

        const message = this.textarea.value.trim();
        const model = this.modelSelect ? this.modelSelect.value : 'default';

        if (message) {
            this.setLoadingState(true);

            try {
                await this.sendMessageStream(message, model);
                this.clearInput();
            } catch (error) {
                this.chatManager.showError('Ошибка при отправке сообщения: ' + error.message);
            } finally {
                this.setLoadingState(false);
            }
        }
    }

    setLoadingState(isLoading) {
        if (this.sendButton) {
            this.sendButton.disabled = isLoading;
            if (isLoading) {
                this.sendButton.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div>';
                this.sendButton.style.backgroundColor = '#30363d';
            } else {
                this.sendButton.innerHTML = '<img src="/images/send.svg" alt="Отправить">';
                this.sendButton.style.backgroundColor = '';
            }
        }
        if (this.textarea) {
            this.textarea.disabled = isLoading;
        }
    }
    async sendMessageToServer(message, model) {
        let currentChatId = this.chatManager.getCurrentChatId();

        // Если нет активного чата, создаем временный чат
        if (!currentChatId || currentChatId === '/' || currentChatId === 'new-chat') {
            if (window.app && window.app.sidebar) {
                window.app.sidebar.createNewChat();
                currentChatId = 'new-chat';
                this.chatManager.setCurrentChatId(currentChatId);
            }
        }

        // Добавляем сообщение пользователя в историю
        this.chatManager.addMessage(message, 'user');

        try {

            const response = await fetch('/postRequest', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.chatManager.getCsrfToken()
                },
                body: JSON.stringify({
                    prompt: message,
                    model: model,
                    chatID: currentChatId
                })
            });


            const data = await response.json();

            // Если есть ошибка в ответе - бросаем исключение
            if (!response.ok || data.error) {
                throw new Error(data.error || `Ошибка сервера: ${response.status}`);
            }

            let responseText = '';
            let newChatId = currentChatId;

            // Обрабатываем успешный ответ сервера
            if (data.response !== undefined && data.response !== null) {
                responseText = this.convertToString(data.response);

                // Если сервер вернул новый ID чата
                if (data.chat_id && data.chat_id !== currentChatId && currentChatId === 'new-chat') {
                    newChatId = data.chat_id;

                    // Обновляем UI с новым ID чата
                    if (window.app && window.app.sidebar) {
                        const chatName = data.chat_name || 'Новый чат';
                        window.app.sidebar.updateTempChatToServerChat(currentChatId, newChatId, chatName);
                    }

                    // Обновляем ID в менеджере
                    this.chatManager.updateChatId(currentChatId, newChatId);
                }
            } else {
                responseText = 'Неизвестный формат ответа';
            }

            // Добавляем ответ ассистента в историю
            this.chatManager.addMessage(responseText, 'assistant');

        } catch (error) {
            const errorMessage = this.convertToString(error.message);
            this.chatManager.addMessage(errorMessage, 'error');
        }
    }
    /**
     * Отправка сообщения с потоковым получением ответа
     */
    async sendMessageStream(message, model) {
        let currentChatId = this.chatManager.getCurrentChatId();

        // Если нет активного чата, создаем временный чат
        if (!currentChatId || currentChatId === '/' || currentChatId === 'new-chat') {
            if (window.app && window.app.sidebar) {
                window.app.sidebar.createNewChat();
                currentChatId = 'new-chat';
                this.chatManager.setCurrentChatId(currentChatId);
            }
        }

        // Добавляем сообщение пользователя в историю
        this.chatManager.addMessage(message, 'user');

        // Создаем элемент для потокового ответа
        const assistantMessageElement = this.chatManager.createStreamingMessage();
        let accumulatedText = '';

        try {

            const response = await fetch('/postRequest', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/event-stream',
                    'X-CSRF-TOKEN': this.chatManager.getCsrfToken()
                },
                body: JSON.stringify({
                    prompt: message,
                    model: model,
                    chatID: currentChatId
                })
            });


            // ⚡ ПРОВЕРЯЕМ HTTP СТАТУС ОШИБКИ
            if (!response.ok) {
                // Если статус 500, пытаемся прочитать streaming ошибку
                if (response.status === 500) {
                    await this.handleStreamError(response, assistantMessageElement);
                    return;
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Чтение Server-Sent Events
            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';

            while (true) {
                const { done, value } = await reader.read();

                if (done) {
                    break;
                }

                const chunk = decoder.decode(value, { stream: true });
                buffer += chunk;

                const lines = buffer.split('\n\n');
                buffer = lines.pop() || '';

                for (const line of lines) {
                    if (line.startsWith('data: ')) {
                        try {
                            const eventData = JSON.parse(line.slice(6));

                            switch (eventData.type) {
                                case 'chat_info':
                                    if (eventData.is_new_chat && currentChatId === 'new-chat') {
                                        this.updateChatId(currentChatId, eventData.chat_id, eventData.chat_name);
                                        currentChatId = eventData.chat_id;
                                    }
                                    break;

                                case 'content':
                                    if (eventData.content) {
                                        accumulatedText += eventData.content;
                                        this.chatManager.updateStreamingMessage(assistantMessageElement, accumulatedText);
                                    }
                                    break;

                                case 'complete':
                                    this.chatManager.finalizeStreamingMessage(assistantMessageElement, accumulatedText);

                                    if (eventData.chat_id && currentChatId === 'new-chat') {
                                        this.updateChatId(currentChatId, eventData.chat_id, eventData.chat_name);
                                    }
                                    return;

                                case 'error':
                                    // ⚡ ОБРАБОТКА ОШИБОК ИЗ STREAMING
                                    this.chatManager.updateStreamingMessage(assistantMessageElement,
                                        '❌ ' + eventData.error, 'error');
                                    return;

                                default:
                            }

                        } catch (e) {
                        }
                    }
                }
            }

        } catch (error) {
            this.chatManager.updateStreamingMessage(assistantMessageElement,
                '❌' + error.message, 'error');
        }
    }

    async handleStreamError(response, messageElement) {
        try {
            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let errorMessage = 'Неизвестная ошибка сервера';

            const { value } = await reader.read();
            const chunk = decoder.decode(value);

            const lines = chunk.split('\n\n');
            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    try {
                        const eventData = JSON.parse(line.slice(6));
                        if (eventData.error) {
                            errorMessage = eventData.error;
                        }
                    } catch (e) {
                    }
                }
            }

            this.chatManager.updateStreamingMessage(messageElement,
                '❌ ' + errorMessage, 'error');

        } catch (e) {
            this.chatManager.updateStreamingMessage(messageElement,
                '❌ Ошибка сервера', 'error');
        }
    }
    /**
     * Обновление ID чата
     */
    updateChatId(oldChatId, newChatId, chatName) {

        if (window.app && window.app.sidebar) {
            window.app.sidebar.updateTempChatToServerChat(oldChatId, newChatId, chatName);
        }
        this.chatManager.updateChatId(oldChatId, newChatId);

        // ⚡ Обновляем URL если это новый чат
        if (oldChatId === 'new-chat') {
            window.history.replaceState({}, '', `/chat/${newChatId}`);
        }
    }

    clearInput() {
        if (this.textarea) {
            this.textarea.value = '';
            this.textarea.style.height = 'auto';
            this.textarea.focus();
        }
    }

    convertToString(value) {
        if (value === null || value === undefined) return '';
        if (typeof value === 'string') return value;
        if (typeof value === 'number' || typeof value === 'boolean') return String(value);
        if (typeof value === 'object') {
            try {
                return JSON.stringify(value);
            } catch (e) {
                return String(value);
            }
        }
        return String(value);
    }

    static autoResize(textarea) {
        textarea.style.height = 'auto';
        const maxHeight = 200;
        const newHeight = Math.min(textarea.scrollHeight, maxHeight);
        textarea.style.height = newHeight + 'px';
        textarea.style.overflowY = newHeight >= maxHeight ? 'auto' : 'hidden';
    }

    static sendMessage() {
        if (window.app && window.app.inputHandler) {
            window.app.inputHandler.handleSendMessage();
        }
    }
}
