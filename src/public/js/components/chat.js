import { escapeHtml } from '../utils/helpers.js';

export class ChatManager {
    constructor() {
        this.currentChatId = this.getCurrentChatIdFromURL();
        console.log('Initial current chat ID:', this.currentChatId);

        // Инициализируем Marked и Highlight.js
        this.initMarkdownRenderer();

        this.init();
    }

    initMarkdownRenderer() {
        // Проверяем, что библиотеки загружены
        if (typeof marked === 'undefined') {
            console.error('Marked.js not loaded');
            return;
        }

        if (typeof hljs === 'undefined') {
            console.error('Highlight.js not loaded');
            return;
        }

        // Настраиваем Marked для рендеринга Markdown
        marked.setOptions({
            highlight: (code, lang) => {
                if (lang && hljs.getLanguage(lang)) {
                    try {
                        return hljs.highlight(code, { language: lang }).value;
                    } catch (err) {
                        console.warn(`Highlight.js error for language ${lang}:`, err);
                    }
                }
                return hljs.highlightAuto(code).value;
            },
            langPrefix: 'hljs language-',
            breaks: true,
            gfm: true
        });
    }

    init() {
        if (!this.currentChatId || this.currentChatId === '/') {
            console.log('Root path, showing empty state');
            this.showEmptyState();
            return;
        }

        if (this.currentChatId === 'new-chat') {
            console.log('New chat, showing empty state');
            this.showEmptyState();
            return;
        }

        this.loadChatHistory(this.currentChatId);

        // ДОБАВЛЯЕМ ЭТУ СТРОКУ - инициализируем управление layout
        this.initLayoutHeights();
    }

    getCurrentChatIdFromURL() {
        const path = window.location.pathname;
        console.log('Current path:', path);

        if (path === '/' || path === '') {
            console.log('Root path, no chat ID');
            return null;
        }

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

            // Для AI сообщений рендерим Markdown, для пользователей - простой текст
            const content = role === 'user'
                ? this.escapeAndFormatText(messageText)
                : this.renderMarkdown(messageText);

            historyHTML += `
                <button class="copy-button" title="Скопировать сообщение" data-text="${this.escapeHtmlAttribute(messageText)}">
                    <i class="bi bi-clipboard"></i>
                </button>
                <div class="message-content">
                    ${content}
                </div>
            </div>`;
        });

        historyContainer.innerHTML = historyHTML;

        // Добавляем обработчики для кнопок копирования
        this.bindCopyButtons();

        // Применяем подсветку синтаксиса
        this.applySyntaxHighlighting();

        // ДОБАВЛЯЕМ ПЕРЕСЧЕТ LAYOUT
        this.recalculateLayout();

        console.log('History rendered, messages count:', messages.length);
    }

    renderMarkdown(text) {
        if (!text) return '';

        try {
            if (typeof marked === 'undefined') {
                console.warn('Marked.js not available, using plain text');
                return this.escapeAndFormatText(text);
            }

            // Рендерим Markdown
            let rendered = marked.parse(text);

            // Добавляем кнопки копирования для блоков кода
            rendered = this.addCopyButtonsToCodeBlocks(rendered);

            return rendered;
        } catch (error) {
            console.error('Markdown rendering error:', error);
            return this.escapeAndFormatText(text);
        }
    }

    addCopyButtonsToCodeBlocks(html) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;

        const preBlocks = tempDiv.querySelectorAll('pre');
        preBlocks.forEach((pre, index) => {
            const codeBlock = pre.querySelector('code');
            if (codeBlock) {
                const language = this.getCodeLanguage(codeBlock);

                // Создаем заголовок с кнопкой копирования
                const header = document.createElement('div');
                header.className = 'code-block-header';
                header.innerHTML = `
                <span class="code-language">${language}</span>
                <button class="code-copy-button" data-code-index="${index}" title="Скопировать код">
                    <i class="bi bi-clipboard"></i>
                    <span>Копировать</span>
                </button>
            `;

                // Вставляем заголовок в начало pre блока
                pre.insertBefore(header, pre.firstChild);

                // Убедимся, что у pre правильные стили
                pre.style.margin = '1em 0';
                pre.style.borderRadius = '8px';
                pre.style.overflow = 'hidden';
                pre.style.border = '1px solid #30363d';
                pre.style.background = '#0d1117';
            }
        });

        return tempDiv.innerHTML;
    }
    // В методе getCodeLanguage обновляем логику определения языка
    getCodeLanguage(codeBlock) {
        const className = codeBlock.className || '';

        // Ищем язык в классах
        const match = className.match(/language-(\w+)/);
        if (match) {
            const lang = match[1].toLowerCase();

            // Маппинг языков для красивого отображения
            const languageMap = {
                'js': 'JavaScript',
                'javascript': 'JavaScript',
                'ts': 'TypeScript',
                'typescript': 'TypeScript',
                'py': 'Python',
                'python': 'Python',
                'php': 'PHP',
                'java': 'Java',
                'cpp': 'C++',
                'c': 'C',
                'cs': 'C#',
                'csharp': 'C#',
                'html': 'HTML',
                'xml': 'XML',
                'css': 'CSS',
                'scss': 'SCSS',
                'sass': 'SASS',
                'sql': 'SQL',
                'bash': 'Bash',
                'shell': 'Shell',
                'sh': 'Shell',
                'powershell': 'PowerShell',
                'ps1': 'PowerShell',
                'json': 'JSON',
                'yaml': 'YAML',
                'yml': 'YAML',
                'md': 'Markdown',
                'markdown': 'Markdown',
                'dockerfile': 'Dockerfile',
                'rust': 'Rust',
                'go': 'Go',
                'rb': 'Ruby',
                'ruby': 'Ruby',
                'swift': 'Swift',
                'kotlin': 'Kotlin'
            };

            return languageMap[lang] || lang.toUpperCase();
        }

        // Пытаемся определить язык по содержимому
        const content = codeBlock.textContent || '';
        if (content.includes('<?php') || content.includes('$') && content.includes(';')) {
            return 'PHP';
        } else if (content.includes('function') && content.includes('{') && content.includes('}')) {
            return 'JavaScript';
        } else if (content.includes('def ') && content.includes(':')) {
            return 'Python';
        } else if (content.includes('Write-Host') || content.includes('Get-')) {
            return 'PowerShell';
        }

        return 'CODE';
    }

    escapeAndFormatText(text) {
        if (!text) return '';

        return escapeHtml(text)
            .replace(/\n/g, '<br>');
    }

    escapeHtmlAttribute(text) {
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#x27;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");
    }

    applySyntaxHighlighting() {
        if (typeof hljs === 'undefined') {
            console.warn('Highlight.js not available');
            return;
        }

        document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightElement(block);
        });
    }

    bindCopyButtons() {
        // Кнопки копирования для всего сообщения - ИСПРАВЛЕННЫЙ
        const copyButtons = document.querySelectorAll('.copy-button');
        copyButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();

                // Вместо data-text атрибута, извлекаем текст из отрендеренного содержимого
                const messageContent = button.closest('.message').querySelector('.message-content');
                if (messageContent) {
                    // Извлекаем чистый текст из HTML
                    const textToCopy = this.extractTextFromHTML(messageContent.innerHTML);
                    this.copyToClipboard(textToCopy, button);
                }
            });
        });

        // Кнопки копирования для блоков кода
        const codeCopyButtons = document.querySelectorAll('.code-copy-button');
        codeCopyButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const pre = button.closest('pre');
                if (pre) {
                    const codeBlock = pre.querySelector('code');
                    if (codeBlock) {
                        const textToCopy = codeBlock.textContent || codeBlock.innerText;
                        this.copyToClipboard(textToCopy.trim(), button);
                    }
                }
            });
        });
    }

// Добавляем вспомогательный метод для извлечения текста из HTML
    extractTextFromHTML(html) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;

        // Рекурсивно извлекаем текст из всех узлов
        const extractText = (node) => {
            if (node.nodeType === Node.TEXT_NODE) {
                return node.textContent || '';
            }

            if (node.nodeType === Node.ELEMENT_NODE) {
                // Для блоков кода сохраняем форматирование
                if (node.tagName === 'PRE' || node.tagName === 'CODE') {
                    return '\n```\n' + (node.textContent || '') + '\n```\n';
                }

                // Для других элементов обрабатываем детей
                let text = '';
                for (const child of node.childNodes) {
                    text += extractText(child);
                }

                // Добавляем переносы строк для блочных элементов
                const blockElements = ['DIV', 'P', 'BR', 'LI', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6'];
                if (blockElements.includes(node.tagName)) {
                    text += '\n';
                }

                return text;
            }

            return '';
        };

        let text = extractText(tempDiv);

        // Очищаем лишние переносы строк
        text = text.replace(/\n{3,}/g, '\n\n').trim();

        return text;
    }

    async copyToClipboard(text, button) {
        try {
            await navigator.clipboard.writeText(text);

            // Визуальная обратная связь
            const originalHTML = button.innerHTML;

            if (button.classList.contains('code-copy-button')) {
                button.innerHTML = '<i class="bi bi-check"></i> Скопировано';
            } else {
                button.innerHTML = '<i class="bi bi-check"></i>';
            }

            button.classList.add('copied');

            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.classList.remove('copied');
            }, 2000);

        } catch (err) {
            console.error('Ошибка копирования: ', err);
            // Fallback для старых браузеров
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);

            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check"></i>';
            button.classList.add('copied');

            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.classList.remove('copied');
            }, 2000);
        }
    }

    addMessage(text, type = 'assistant') {
        const historyContainer = document.getElementById('history-container');
        if (!historyContainer) return;

        const messageText = this.convertToString(text);

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type === 'user' || type === 'user-message' ? 'user-message' : 'ai-message'}`;

        // Добавляем кнопку копирования
        const copyButton = document.createElement('button');
        copyButton.className = 'copy-button';
        copyButton.title = 'Скопировать сообщение';
        copyButton.setAttribute('data-text', messageText);
        copyButton.innerHTML = '<i class="bi bi-clipboard"></i>';

        copyButton.addEventListener('click', (e) => {
            e.stopPropagation();
            this.copyToClipboard(messageText, copyButton);
        });

        const messageContent = document.createElement('div');
        messageContent.className = 'message-content';

        // Для AI сообщений используем Markdown, для пользователей - простой текст
        if (type === 'user' || type === 'user-message') {
            messageContent.innerHTML = this.escapeAndFormatText(messageText);
        } else {
            messageContent.innerHTML = this.renderMarkdown(messageText);
        }

        messageDiv.appendChild(copyButton);
        messageDiv.appendChild(messageContent);
        historyContainer.appendChild(messageDiv);

        // Применяем подсветку синтаксиса для нового сообщения
        setTimeout(() => {
            this.applySyntaxHighlighting();
        }, 0);

        // ДОБАВЛЯЕМ ВЫЗОВ ПЕРЕСЧЕТА LAYOUT
        this.recalculateLayout();

        console.log('Message added:', { type, text: messageText.substring(0, 50) + '...' });
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

    updateChatId(oldChatId, newChatId) {
        if (this.currentChatId === oldChatId) {
            this.currentChatId = newChatId;
        }
        console.log('Chat ID updated from', oldChatId, 'to', newChatId);
    }

    isTempChat(chatId = null) {
        const targetChatId = chatId || this.currentChatId;
        return !targetChatId || targetChatId === 'new-chat' || targetChatId.startsWith('new-');
    }

    clearHistory() {
        const historyContainer = document.getElementById('history-container');
        if (historyContainer) {
            historyContainer.innerHTML = `<div class="text-center text-muted p-4">Начните новый диалог</div>`;
        }
    }

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

    // === ДОБАВЛЯЕМ НОВЫЕ МЕТОДЫ ДЛЯ УПРАВЛЕНИЯ LAYOUT ===

    /**
     * Инициализация управления высотами layout
     */
    initLayoutHeights() {
        // Вызываем сразу при инициализации
        this.updateLayoutHeights();

        // Добавляем обработчики событий
        window.addEventListener('resize', () => {
            setTimeout(() => this.updateLayoutHeights(), 100);
        });

        // Также обновляем при полной загрузке страницы
        window.addEventListener('load', () => {
            setTimeout(() => this.updateLayoutHeights(), 200);
        });

        // Обновляем при изменении содержимого
        const observer = new MutationObserver(() => {
            this.updateLayoutHeights();
        });

        const historyContainer = document.getElementById('history-container');
        if (historyContainer) {
            observer.observe(historyContainer, {
                childList: true,
                subtree: true
            });
        }
    }

    /**
     * Обновление высот контейнеров для предотвращения перекрытия
     */
    updateLayoutHeights() {
        const header = document.querySelector('header');
        const container = document.getElementById('container');
        const historyContainer = document.getElementById('history-container');
        const inputArea = document.getElementById('input-area');

        if (!header || !container || !historyContainer || !inputArea) {
            console.log('Layout elements not found, retrying...');
            return;
        }

        const headerHeight = header.offsetHeight;
        const inputAreaHeight = inputArea.offsetHeight;

        console.log('Updating layout heights:', {
            headerHeight,
            inputAreaHeight
        });

        // Устанавливаем высоту основного контейнера
        container.style.height = `calc(100vh - ${headerHeight}px)`;
        container.style.minHeight = `calc(100vh - ${headerHeight}px)`;

        // Устанавливаем высоту контейнера истории
        historyContainer.style.height = `calc(100% - ${inputAreaHeight}px)`;
        historyContainer.style.maxHeight = `calc(100% - ${inputAreaHeight}px)`;

        // Принудительно применяем flex layout
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
    }

    /**
     * Пересчет layout после добавления сообщения
     */
    recalculateLayout() {
        setTimeout(() => {
            this.updateLayoutHeights();
            this.scrollToBottom();
        }, 50);
    }

}
