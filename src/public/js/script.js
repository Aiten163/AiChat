function autoResize(textarea) {
    // Сбрасываем высоту чтобы получить правильный scrollHeight
    textarea.style.height = 'auto';

    // Устанавливаем высоту based on scrollHeight с максимальным ограничением
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
                } else if (data.response) {
                    responseText = data.response;
                } else if (data.choices && data.choices[0] && data.choices[0].text) {
                    responseText = data.choices[0].text;
                } else if (data.message) {
                    responseText = data.message;
                } else {
                    responseText = JSON.stringify(data);
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
        .replace(/ /g, '&nbsp;') // Заменяем пробелы на неразрывные
        .replace(/\n/g, '<br>') // Заменяем переносы строк на <br>
        .replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;'); // Заменяем табы на 4 пробела

    messageDiv.innerHTML = formattedText;

    historyContainer.appendChild(messageDiv);
    historyContainer.scrollTop = historyContainer.scrollHeight;
}
