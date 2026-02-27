import { escapeHtml } from '../utils/helpers.js';

export class ChatManager {
    constructor() {
        this.currentChatId = this.getCurrentChatIdFromURL();
        this.highlightedElements = new Set();

        this.initMarkdownRenderer();
        this.init();
    }
    resetHighlighting() {
        if (typeof hljs === 'undefined') return;

        this.highlightedElements.forEach(element => {
            if (element && element.parentNode) {
                element.removeAttribute('data-highlighted');
            }
        });
        this.highlightedElements.clear();
    }

    applySyntaxHighlighting() {
        if (typeof hljs === 'undefined') {
            return;
        }

        this.resetHighlighting();

        const codeBlocks = document.querySelectorAll('pre code:not([data-highlighted])');

        codeBlocks.forEach((block) => {
            try {
                block.setAttribute('data-highlighted', 'true');
                this.highlightedElements.add(block);

                hljs.highlightElement(block);
            } catch (error) {
                console.warn('Highlight error:', error);
                block.removeAttribute('data-highlighted');
            }
        });
    }

    initMarkdownRenderer() {
        if (typeof marked === 'undefined') {
            return;
        }

        if (typeof hljs === 'undefined') {
            return;
        }

        marked.setOptions({
            highlight: (code, lang) => {
                if (lang && hljs.getLanguage(lang)) {
                    try {
                        return hljs.highlight(code, { language: lang }).value;
                    } catch (err) {
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
            this.showEmptyState();
            return;
        }

        if (this.currentChatId === 'new-chat') {
            this.showEmptyState();
            return;
        }

        this.loadChatHistory(this.currentChatId);

        this.initLayoutHeights();
    }
    updateMessageContent(messageElement, content) {
        const contentElement = messageElement.querySelector('.message-content');
        if (contentElement) {
            contentElement.innerHTML = this.formatMessage(content);
        }
        this.scrollToBottom();
    }
    getCurrentChatIdFromURL() {
        const path = window.location.pathname;

        const pathParts = path.replace(/^\/|\/$/g, '').split('/');
        const chatId = pathParts[pathParts.length - 1];

        return chatId || null;
    }

    showEmptyState() {
        const historyContainer = document.getElementById('history-container');
        if (!historyContainer) {
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

        if (!targetChatId || targetChatId === 'new-chat') {
            this.showEmptyState();
            return;
        }

        const historyContainer = document.getElementById('history-container');
        if (!historyContainer) {
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
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(messages => {
                this.renderChatHistory(messages);
                this.currentChatId = targetChatId;
            })
            .catch(error => {
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
            return;
        }

        if (!Array.isArray(messages)) {
            historyContainer.innerHTML = `<div class="text-center text-warning p-4">Неверный формат данных</div>`;
            return;
        }

        if (messages.length === 0) {
            historyContainer.innerHTML = `<div class="text-center text-muted p-4">История чата пуста</div>`;
            return;
        }

        let historyHTML = '';

        messages.forEach((message, index) => {

            const messageText = message.content || message.response || message.text || message.message || '';
            const role = message.role || (message.is_user ? 'user' : 'assistant') || (message.sender === 'user' ? 'user' : 'assistant');

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
    }


    showError(message) {
        const historyContainer = document.getElementById('history-container');
        if (!historyContainer) return;

        const errorMessage = this.convertToString(message);
        historyContainer.innerHTML = `
            <div class="text-center text-danger p-4">
                ${this.escapeAndFormatText(errorMessage)}
            </div>
        `;

        this.recalculateLayout();
    }
    renderMarkdown(text) {
        if (!text) return '';

        try {
            if (typeof marked === 'undefined') {
                return this.escapeAndFormatText(text);
            }

            let rendered = marked.parse(text);

            rendered = this.addCopyButtonsToCodeBlocks(rendered);

            return rendered;
        } catch (error) {
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

                const header = document.createElement('div');
                header.className = 'code-block-header';
                header.innerHTML = `
                <span class="code-language">${language}</span>
                <button class="code-copy-button" data-code-index="${index}" title="Скопировать код">
                    <i class="bi bi-clipboard"></i>
                    <span>Копировать</span>
                </button>
            `;

                pre.insertBefore(header, pre.firstChild);

                pre.style.margin = '1em 0';
                pre.style.borderRadius = '8px';
                pre.style.overflow = 'hidden';
                pre.style.border = '1px solid #30363d';
                pre.style.background = '#0d1117';
            }
        });

        return tempDiv.innerHTML;
    }
    getCodeLanguage(codeBlock) {
        const className = codeBlock.className || '';

        const match = className.match(/language-(\w+)/);
        if (match) {
            const lang = match[1].toLowerCase();

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

    bindCopyButtons() {
        const copyButtons = document.querySelectorAll('.copy-button');
        copyButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();

                const messageContent = button.closest('.message').querySelector('.message-content');
                if (messageContent) {
                    const textToCopy = this.extractTextFromHTML(messageContent.innerHTML);
                    this.copyToClipboard(textToCopy, button);
                }
            });
        });

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

    extractTextFromHTML(html) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;

        const extractText = (node) => {
            if (node.nodeType === Node.TEXT_NODE) {
                return node.textContent || '';
            }

            if (node.nodeType === Node.ELEMENT_NODE) {
                if (node.tagName === 'PRE' || node.tagName === 'CODE') {
                    return '\n```\n' + (node.textContent || '') + '\n```\n';
                }

                let text = '';
                for (const child of node.childNodes) {
                    text += extractText(child);
                }

                const blockElements = ['DIV', 'P', 'BR', 'LI', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6'];
                if (blockElements.includes(node.tagName)) {
                    text += '\n';
                }

                return text;
            }

            return '';
        };

        let text = extractText(tempDiv);

        text = text.replace(/\n{3,}/g, '\n\n').trim();

        return text;
    }

    async copyToClipboard(text, button) {
        try {
            await navigator.clipboard.writeText(text);

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
        if (type === 'streaming') {
            return this.createStreamingMessage();
        }

        const historyContainer = document.getElementById('history-container');
        if (!historyContainer) return;

        const messageText = this.convertToString(text);

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type === 'user' || type === 'user-message' ? 'user-message' : 'ai-message'}`;

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

        if (type === 'user' || type === 'user-message') {
            messageContent.innerHTML = this.escapeAndFormatText(messageText);
        } else {
            messageContent.innerHTML = this.renderMarkdown(messageText);
        }

        messageDiv.appendChild(copyButton);
        messageDiv.appendChild(messageContent);
        historyContainer.appendChild(messageDiv);

        setTimeout(() => {
            this.applySyntaxHighlighting();
        }, 0);

        this.recalculateLayout();
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

    initLayoutHeights() {
        this.updateLayoutHeights();

        window.addEventListener('resize', () => {
            setTimeout(() => this.updateLayoutHeights(), 100);
        });

        window.addEventListener('load', () => {
            setTimeout(() => this.updateLayoutHeights(), 200);
        });

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
    createStreamingMessage() {
        const historyContainer = document.getElementById('history-container');
        if (!historyContainer) return null;

        const messageDiv = document.createElement('div');
        messageDiv.className = 'message ai-message streaming';

        const messageContent = document.createElement('div');
        messageContent.className = 'message-content';
        messageContent.textContent = '';

        messageDiv.appendChild(messageContent);
        historyContainer.appendChild(messageDiv);

        this.recalculateLayout();
        return messageDiv;
    }

    updateStreamingMessage(messageElement, content, type = 'assistant') {
        if (!messageElement) return;

        const contentElement = messageElement.querySelector('.message-content');
        if (contentElement) {
            if (type === 'error') {
                contentElement.innerHTML = this.escapeAndFormatText(content);
                messageElement.className = 'message error-message';
            } else {
                contentElement.innerHTML = this.renderMarkdown(content);
                messageElement.className = 'message ai-message streaming';
            }
        }

        this.applySyntaxHighlighting();
        this.scrollToBottom();
    }

    finalizeStreamingMessage(messageElement, content) {
        if (!messageElement) return;

        const contentElement = messageElement.querySelector('.message-content');
        if (contentElement) {
            contentElement.innerHTML = this.renderMarkdown(content);
        }

        messageElement.classList.remove('streaming');
        this.applySyntaxHighlighting();
        this.scrollToBottom();
    }

    updateLayoutHeights() {
        const header = document.querySelector('header');
        const container = document.getElementById('container');
        const historyContainer = document.getElementById('history-container');
        const inputArea = document.getElementById('input-area');

        if (!header || !container || !historyContainer || !inputArea) {
            return;
        }

        const headerHeight = header.offsetHeight;
        const inputAreaHeight = inputArea.offsetHeight;

        container.style.height = `calc(100vh - ${headerHeight}px)`;
        container.style.minHeight = `calc(100vh - ${headerHeight}px)`;

        historyContainer.style.height = `calc(100% - ${inputAreaHeight}px)`;
        historyContainer.style.maxHeight = `calc(100% - ${inputAreaHeight}px)`;

        container.style.display = 'flex';
        container.style.flexDirection = 'column';
    }


    recalculateLayout() {
        setTimeout(() => {
            this.updateLayoutHeights();
            this.scrollToBottom();
        }, 50);
    }

}
