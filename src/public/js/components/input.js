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
        // Отправка по Enter (без Shift)
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            this.handleSendMessage();
        }

        // Новая строка по Shift+Enter
        if (e.key === 'Enter' && e.shiftKey) {
            // Разрешаем стандартное поведение - вставка новой строки
            return;
        }
    }

    async handleSendMessage() {
        if (!this.textarea) return;

        const message = this.textarea.value.trim();
        const model = this.modelSelect ? this.modelSelect.value : 'default';

        if (message) {
            // Блокируем кнопку отправки и textarea
            this.setLoadingState(true);

            try {
                await this.sendMessageToServer(message, model);
                this.clearInput();
            } catch (error) {
                console.error('Error sending message:', error);
                this.chatManager.showError('Ошибка при отправке сообщения: ' + error.message);
            } finally {
                // Разблокируем кнопку отправки и textarea
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
            console.log('No active chat, creating temporary chat');
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

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Полученные данные:', data);

            let responseText = '';
            let newChatId = currentChatId;

            // Обрабатываем ответ сервера
            if (data.error) {
                responseText = 'Ошибка: ' + data.error;
            } else if (data.response !== undefined && data.response !== null) {
                responseText = this.convertToString(data.response);

                // Если сервер вернул новый ID чата (при создании нового чата)
                if (data.chat_id && data.chat_id !== currentChatId && currentChatId === 'new-chat') {
                    newChatId = data.chat_id;
                    console.log('Server created new chat with ID:', newChatId);

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
            console.error('Error:', error);
            const errorMessage = this.convertToString(error.message);
            this.chatManager.addMessage('Ошибка при отправке сообщения: ' + errorMessage, 'error');
        }
    }

    convertToString(value) {
        if (value === null || value === undefined) {
            return '';
        }
        if (typeof value === 'string') {
            return value;
        }
        if (typeof value === 'number' || typeof value === 'boolean') {
            return String(value);
        }
        if (typeof value === 'object') {
            try {
                return JSON.stringify(value);
            } catch (e) {
                return String(value);
            }
        }
        return String(value);
    }

    clearInput() {
        if (this.textarea) {
            this.textarea.value = '';
            this.textarea.style.height = 'auto';
            // Возвращаем фокус на textarea после отправки
            this.textarea.focus();
        }
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
