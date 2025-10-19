import { escapeHtml } from '../utils/helpers.js';

export class ChatManager {
    constructor() {
        this.currentChatId = this.getCurrentChatIdFromURL();
        console.log('Initial current chat ID:', this.currentChatId);
        this.init();
    }

    init() {
        // Если в URL корневой путь "/", показываем пустой экран
        if (!this.currentChatId || this.currentChatId === '/') {
            console.log('Root path, showing empty state');
            this.showEmptyState();
            return;
        }

        // Если это новый чат, показываем пустой экран
        if (this.currentChatId === 'new-chat') {
            console.log('New chat, showing empty state');
            this.showEmptyState();
            return;
        }

        // Загружаем историю только для существующих чатов
        this.loadChatHistory(this.currentChatId);
    }

    getCurrentChatIdFromURL() {
        const path = window.location.pathname;
        console.log('Current path:', path);

        // Если путь просто "/", возвращаем null
        if (path === '/' || path === '') {
            console.log('Root path, no chat ID');
            return null;
        }

        // Убираем начальный и конечный слэши и получаем последнюю часть
        const pathParts = path.replace(/^\/|\/$/g, '').split('/');
        const chatId = pathParts[pathParts.length - 1];
        console.log('Extracted chat ID:', chatId);

        return chatId || null;
    }

    showEmptyState() {
        const historyContainer = document.getElementById('history-container');
        if (!historyContainer) {
            console.error('History container not found');
            return;
        }

        historyContainer.innerHTML = `
            <div class="text-center text-muted p-4">
                <div class="mb-3">
                    <i class="bi bi-chat-dots" style="font-size: 3rem;"></i>
                </div>
                <h5 class="mb-2">Добро пожаловать в AI Chat</h5>
                <p class="mb-3">Начните новый диалог или выберите существующий чат из списка</p>
            </div>
        `;
    }

    loadChatHistory(chatId = null) {
        const targetChatId = chatId || this.currentChatId;
        console.log('Loading history for chat ID:', targetChatId);

        if (!targetChatId || targetChatId === 'new-chat') {
            console.log('New chat or no chat ID, showing empty state');
            this.showEmptyState();
            return;
        }

        const historyContainer = document.getElementById('history-container');
        if (!historyContainer) {
            console.error('History container not found');
            return;
        }

        historyContainer.innerHTML = `<div class="text-center text-muted p-4">Загрузка истории чата ...</div>`;

        fetch(`/getHistoryChat?chat_id=${targetChatId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.getCsrfToken()
            }
        })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(messages => {
                console.log('Loaded messages:', messages);
                this.renderChatHistory(messages);
                this.currentChatId = targetChatId;
                console.log('Current chat ID updated to:', this.currentChatId);
            })
            .catch(error => {
                console.error('Ошибка загрузки истории:', error);
                historyContainer.innerHTML = `
                <div class="text-center text-danger p-4">
                    Ошибка загрузки истории чата: ${error.message}
                </div>
            `;
            });
    }

    renderChatHistory(messages) {
        const historyContainer = document.getElementById('history-container');
        if (!historyContainer) {
            console.error('History container not found');
            return;
        }

        if (!Array.isArray(messages)) {
            console.error('Ожидался массив сообщений, получено:', messages);
            historyContainer.innerHTML = `<div class="text-center text-warning p-4">Неверный формат данных</div>`;
            return;
        }

        if (messages.length === 0) {
            historyContainer.innerHTML = `<div class="text-center text-muted p-4">История чата пуста</div>`;
            return;
        }

        let historyHTML = '';

        messages.forEach((message, index) => {
            console.log(`Message ${index}:`, message);

            const messageText = message.content || message.response || message.text || message.message || '';
            const role = message.role || (message.is_user ? 'user' : 'assistant') || (message.sender === 'user' ? 'user' : 'assistant');

            console.log(`Message ${index} role:`, role, 'text:', messageText);

            if (role === 'user') {
                historyHTML += '<div class="message user-message">';
            } else {
                historyHTML += '<div class="message ai-message">';
            }

            historyHTML += `
                <div class="message-content">
                    <p>${escapeHtml(messageText)}</p>
                </div>
            </div>`;
        });

        historyContainer.innerHTML = historyHTML;

        // Скроллим вниз после рендера
        setTimeout(() => {
            this.scrollToBottom();
        }, 100);

        console.log('History rendered, messages count:', messages.length);
    }

    addMessage(text, type = 'assistant') {
        const historyContainer = document.getElementById('history-container');
        if (!historyContainer) return;

        // Преобразуем text в строку
        const messageText = this.convertToString(text);

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type === 'user' || type === 'user-message' ? 'user-message' : 'ai-message'}`;

        // Устанавливаем стили по отдельности
        messageDiv.style.marginBottom = '1rem';
        messageDiv.style.padding = '0.75rem 1rem';
        messageDiv.style.borderRadius = '12px';
        messageDiv.style.maxWidth = '80%';
        messageDiv.style.wordWrap = 'break-word';
        messageDiv.style.color = '#e6edf3';

        if (type === 'user' || type === 'user-message') {
            messageDiv.style.backgroundColor = 'rgb(57, 88, 133)';
            messageDiv.style.marginLeft = 'auto';
            messageDiv.style.border = '1px solid rgba(86, 125, 186, 0.3)';
        } else {
            messageDiv.style.backgroundColor = 'rgb(33, 38, 45)';
            messageDiv.style.border = '1px solid #30363d';
            messageDiv.style.marginRight = 'auto';
        }

        const messageContent = document.createElement('div');
        messageContent.className = 'message-content';

        const paragraph = document.createElement('p');
        paragraph.innerHTML = this.formatMessageText(messageText);

        messageContent.appendChild(paragraph);
        messageDiv.appendChild(messageContent);

        historyContainer.appendChild(messageDiv);

        // Скроллим вниз после добавления сообщения
        setTimeout(() => {
            this.scrollToBottom();
        }, 50);

        console.log('Message added:', { type, text: messageText.substring(0, 50) + '...' });

        // Отладочная информация
        console.log('Message element styles:', {
            backgroundColor: messageDiv.style.backgroundColor,
            border: messageDiv.style.border,
            marginLeft: messageDiv.style.marginLeft,
            marginRight: messageDiv.style.marginRight
        });
    }

    formatMessageText(text) {
        if (!text) return '';

        // Убеждаемся, что text - строка
        const textString = this.convertToString(text);

        return textString
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;")
            .replace(/\n/g, '<br>')
            .replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;');
    }

    // Универсальная функция преобразования в строку
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

    scrollToBottom() {
        const historyContainer = document.getElementById('history-container');
        if (historyContainer) {
            historyContainer.scrollTop = historyContainer.scrollHeight;
        }
    }

    getCurrentChatId() {
        return this.currentChatId;
    }

    setCurrentChatId(chatId) {
        console.log('Setting current chat ID from', this.currentChatId, 'to', chatId);
        this.currentChatId = chatId;
    }

    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ||
            document.querySelector('input[name="_token"]')?.value || '';
    }

    // Метод для обновления ID чата после создания на сервере
    updateChatId(oldChatId, newChatId) {
        if (this.currentChatId === oldChatId) {
            this.currentChatId = newChatId;
        }
        console.log('Chat ID updated from', oldChatId, 'to', newChatId);
    }

    // Метод для проверки, является ли чат временным
    isTempChat(chatId = null) {
        const targetChatId = chatId || this.currentChatId;
        return !targetChatId || targetChatId === 'new-chat' || targetChatId.startsWith('new-');
    }

    // Метод для очистки истории (для новых чатов)
    clearHistory() {
        const historyContainer = document.getElementById('history-container');
        if (historyContainer) {
            historyContainer.innerHTML = `<div class="text-center text-muted p-4">Начните новый диалог</div>`;
        }
    }

    // Метод для отображения ошибки
    showError(message) {
        const historyContainer = document.getElementById('history-container');
        if (historyContainer) {
            const errorMessage = this.convertToString(message);
            historyContainer.innerHTML = `
                <div class="text-center text-danger p-4">
                    ${escapeHtml(errorMessage)}
                </div>
            `;
        }
    }
}
