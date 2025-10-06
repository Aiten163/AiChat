<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="bootstrap/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href={{ asset("css/style.css") }}>
</head>
<body>
<header class="w-100 p-3 bg-dark border-bottom border-secondary">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <!-- Кнопка меню -->
                <button id="menu-toggle" class="btn btn-outline-light btn-sm me-3">
                    <i class="bi bi-list"></i>
                </button>

                <h1 class="h4 mb-0 text-white fw-light">
                    <i class="bi bi-robot me-2"></i> AI Chat
                </h1>
            </div>
            <div class="col-auto">
                @auth
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-white-50 fs-6"><span class="text-white fw-medium">{{ Auth::user()->name }}</span></span>

                        <div class="vr bg-secondary" style="height: 24px;"></div>

                        <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm border-0">
                                <i class="bi bi-box-arrow-right me-1"></i>Выйти
                            </button>
                        </form>

                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('platform.users.list') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-speedometer2 me-1"></i>Админка
                            </a>
                        @endif
                    </div>
                @else
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Вход
                    </button>
                @endauth

                <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-dark border border-secondary">
                            <div class="modal-header border-secondary">
                                <h5 class="modal-title text-white fs-5" id="loginModalLabel">
                                    <i class="bi bi-key me-2"></i>Авторизация
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('login') }}" method="post" class="needs-validation" novalidate>
                                    @csrf

                                    <div class="mb-4">
                                        <label for="name" class="form-label text-white-50 mb-2">Логин</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-secondary border-secondary text-white">
                                                <i class="bi bi-person"></i>
                                            </span>
                                            <input type="text" name="name" id="name" class="form-control bg-dark border-secondary text-white"
                                                   placeholder="Введите ваш логин" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="password" class="form-label text-white-50 mb-2">Пароль</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-secondary border-secondary text-white">
                                                <i class="bi bi-lock"></i>
                                            </span>
                                            <input type="password" name="password" id="password" class="form-control bg-dark border-secondary text-white"
                                                   placeholder="Введите ваш пароль" required>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Войти в систему
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Выдвижное меню -->
<div id="sidebar">
    <div class="sidebar-header">
        <h5 class="text-white mb-3">Чаты</h5>
        <button class="btn btn-primary btn-sm w-100" onclick="createNewChat()">
            <i class="bi bi-plus-circle me-1"></i> Новый чат
        </button>
    </div>

    <ul class="chat-list" id="chat-list">
        @foreach($chats as $chat)
            <li class="chat-item active" data-chat-id="1">
                <div class="chat-name">{{$chat->name}}</div>
            </li>
        @endforeach
    </ul>
</div>

<!-- Оверлей для закрытия меню на мобильных -->
<div id="sidebar-overlay" class="sidebar-overlay"></div>

@auth
    <div id='container' class="container-fluid">
        <div id='history-container' class="messages-container">
            <!-- История сообщений -->
        </div>

        <div id='input-area'>
            <div id='input-wrapper'>
                <div id='input-model'>
                    <label>
                        <select name="model">
                            @if($neurals)
                                <option>Нейросети не загружены</option>
                            @endif

                            @foreach($neurals as $neural)
                                <option value="{{$neural['name']}}">{{$neural['show_name']}}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div id="text-request">
                        <textarea
                            placeholder="Введите ваш запрос..."
                            rows="1"
                            oninput="autoResize(this)"
                        ></textarea>
                </div>

                <div id="button-send">
                    <button onclick="sendMessage()">
                        <img src="{{ asset('images/send.svg') }}" alt="Отправить">
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const container = document.getElementById('container');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const chatItems = document.querySelectorAll('.chat-item');

        // Открытие/закрытие меню
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            container.classList.toggle('sidebar-open');
            sidebarOverlay.classList.toggle('open');
        });

        // Закрытие меню при клике на оверлей
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            container.classList.remove('sidebar-open');
            sidebarOverlay.classList.remove('open');
        });

        // Выбор чата
        chatItems.forEach(item => {
            item.addEventListener('click', function() {
                // Убираем активный класс у всех чатов
                chatItems.forEach(chat => chat.classList.remove('active'));

                // Добавляем активный класс к выбранному чату
                this.classList.add('active');

                // Получаем ID выбранного чата
                const chatId = this.getAttribute('data-chat-id');

                // Загружаем историю выбранного чата
                loadChatHistory(chatId);

                // На мобильных устройствах закрываем меню после выбора
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('open');
                    container.classList.remove('sidebar-open');
                    sidebarOverlay.classList.remove('open');
                }
            });
        });

        function loadChatHistory(chatId) {
            const historyContainer = document.getElementById('history-container');
            historyContainer.innerHTML = `<div class="text-center text-muted p-4">Загрузка истории чата ...</div>`;

            console.log('Загрузка истории для чата:', chatId);

            fetch(`/getHistoryChat?chat_id=${chatId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    console.log('Статус ответа:', response.status);
                    console.log('Заголовки ответа:', response.headers);

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    // Получаем текст ответа для отладки
                    return response.text();
                })
                .then(text => {
                    console.log('Полученный ответ:', text);

                    // Пытаемся распарсить JSON только если есть содержимое
                    if (!text.trim()) {
                        throw new Error('Пустой ответ от сервера');
                    }

                    const messages = JSON.parse(text);
                    renderChatHistory(messages);
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

        function renderChatHistory(messages) {
            const historyContainer = document.getElementById('history-container');

            if (!Array.isArray(messages)) {
                console.error('Ожидался массив сообщений, получено:', messages);
                historyContainer.innerHTML = `<div class="text-center text-warning p-4">Неверный формат данных</div>`;
                return;
            }

            if (messages.length === 0) {
                historyContainer.innerHTML = `<div class="text-center text-muted p-4">История чата пуста</div>`;
                return;
            }

            let history = '';

            messages.forEach((message) => {
                // Проверяем структуру сообщения
                console.log('Обработка сообщения:', message);

                const messageText = message.content || message.response || message.text || '';
                const role = message.role || 'assistant';

                if (role === 'user') {
                    history += '<div class="message user-message">';
                } else {
                    history += '<div class="message ai-message">';
                }

                history += `
            <div class="message-content">
                <p>${escapeHtml(messageText)}</p>
            </div>
        </div>`;
            });

            historyContainer.innerHTML = history;
        }

        // Функция для экранирования HTML
        function escapeHtml(unsafe) {
            if (!unsafe) return '';
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Функция создания нового чата
        function createNewChat() {
            // Здесь будет код для создания нового чата
            console.log('Создание нового чата');

            // Временная демонстрация
            const chatList = document.getElementById('chat-list');

            const newChatItem = document.createElement('li');
            newChatItem.className = 'chat-item active';
            newChatItem.innerHTML = `
            <div class="chat-name">Новый чат</div>
            <div class="chat-preview">Только что создан</div>
        `;

            // Убираем активный класс у всех чатов
            chatItems.forEach(chat => chat.classList.remove('active'));

            // Добавляем новый чат в начало списка
            chatList.insertBefore(newChatItem, chatList.firstChild);

            // Добавляем обработчик события для нового чата
            newChatItem.addEventListener('click', function() {
                chatItems.forEach(chat => chat.classList.remove('active'));
                this.classList.add('active');
                loadChatHistory();

                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('open');
                    container.classList.remove('sidebar-open');
                    sidebarOverlay.classList.remove('open');
                }
            });

            // Загружаем пустую историю для нового чата
            loadChatHistory();
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadChatHistory(1);
        });
    </script>

    <script src="{{ asset('js/script.js') }}" > </script>
@endauth
</body>
</html>
