import { formatText, getCsrfToken } from '../utils/helpers.js';

export function addMessage(text, type) {
    const historyContainer = document.getElementById('history-container');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}-message`;

    const formattedText = formatText(text);
    messageDiv.innerHTML = formattedText;

    historyContainer.appendChild(messageDiv);
    historyContainer.scrollTop = historyContainer.scrollHeight;
}

export function sendMessage(message, model) {
    const inputHandler = window.app?.inputHandler;
    const chatId = inputHandler ? inputHandler.getCurrentChatId() : getCurrentChatIdFromURL();

    addMessage(message, 'user');

    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken()
    };

    fetch('/postRequest', {
        method: 'POST',
        body: JSON.stringify({
            prompt: message,
            model: model,
            chatID: chatId
        }),
        headers: headers
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Полученные данные:', data);

            let responseText = '';
            if (typeof data === 'string') {
                responseText = data;
            } else if (data.response) {
                responseText = data.response;
            } else {
                responseText = 'Ошибка с типом данных';
            }

            addMessage(responseText, 'assistant');
        })
        .catch(error => {
            console.error('Error:', error);
            addMessage('Ошибка при отправке сообщения: ' + error.message, 'error');
        });
}

function getCurrentChatIdFromURL() {
    const path = window.location.pathname;
    return parseInt(path.replace(/^\//, ''), 10) || 'new';
}
