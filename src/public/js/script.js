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
    const message = textarea.value; // Не используем trim() чтобы сохранить пробелы
    const model = document.querySelector('select[name="model"]').value;

    if (message && message.trim() !== '') { // Проверяем что текст не только из пробелов
        addMessage(message, 'user');
        textarea.value = '';
        textarea.style.height = 'auto';

        // Имитация ответа AI
        setTimeout(() => {
            addMessage('Это пример ответа от AI модели...', 'ai');
        }, 1000);
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