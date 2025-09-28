function autoResize(textarea) {
    textarea.style.height = 'auto';

    const maxHeight = 150;
    const newHeight = Math.min(textarea.scrollHeight, maxHeight);

    textarea.style.height = newHeight + 'px';
    textarea.style.overflowY = newHeight >= maxHeight ? 'auto' : 'hidden';
}

// Функция для отправки сообщения
function sendMessage() {
    const textarea = document.querySelector('#text-request textarea');
    const message = textarea.value;
    const model = document.querySelector('select[name="model"]').value;

    if (message && message.trim() !== '') {
        addMessage(message, 'user');
        textarea.value = '';
        textarea.style.height = 'auto';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
            document.querySelector('input[name="_token"]')?.value || '';

        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        fetch('/postRequest', {
            method: 'POST',
            body: JSON.stringify({
                prompt: message,
                model: model
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

                // Правильное извлечение текста ответа
                let responseText = '';

                if (typeof data === 'string') {
                    responseText = data;
                } else {
                    responseText = 'Ошибка с типом данных'
                }

                addMessage(responseText, 'assistant');
            })
            .catch(error => {
                console.error('Error:', error);
                addMessage('Ошибка при отправке сообщения: ' + error.message, 'error');
            });
    }
}

// Функция для добавления сообщения в историю
function addMessage(text, type) {
    const historyContainer = document.getElementById('history-container');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}-message`;

    // Сохраняем форматирование текста
    const formattedText = text
        .replace(/ /g, '&nbsp;')
        .replace(/\n/g, '<br>')
        .replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;'); // Заменяем табы на 4 пробела

    messageDiv.innerHTML = formattedText;

    historyContainer.appendChild(messageDiv);
    historyContainer.scrollTop = historyContainer.scrollHeight;
}
