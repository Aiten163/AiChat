export class Sidebar {
    constructor(chatManager) {
        this.chatManager = chatManager;
        this.sidebar = document.getElementById('sidebar');
        this.container = document.getElementById('container');
        this.sidebarOverlay = document.getElementById('sidebar-overlay');
        this.menuToggle = document.getElementById('menu-toggle');

        this.init();
    }

    init() {
        this.bindEvents();
        this.markCurrentChat();
    }

    bindEvents() {
        // Открытие/закрытие меню
        if (this.menuToggle) {
            this.menuToggle.addEventListener('click', () => this.toggleSidebar());
        }

        // Закрытие меню при клике на оверлей
        if (this.sidebarOverlay) {
            this.sidebarOverlay.addEventListener('click', () => this.closeSidebar());
        }

        // Выбор чата
        this.bindChatItems();

        // Обработка изменений истории браузера (назад/вперед)
        window.addEventListener('popstate', () => {
            const newChatId = this.chatManager.getCurrentChatIdFromURL();
            console.log('URL changed to chat ID:', newChatId);
            this.chatManager.setCurrentChatId(newChatId);

            if (!newChatId || newChatId === '/') {
                this.chatManager.showEmptyState();
            } else if (newChatId === 'new-chat') {
                this.chatManager.showEmptyState();
            } else {
                this.chatManager.loadChatHistory(newChatId);
            }

            this.markCurrentChat();
        });
    }

    markCurrentChat() {
        const currentChatId = this.chatManager.getCurrentChatIdFromURL();
        console.log('Marking current chat:', currentChatId);

        const chatItems = document.querySelectorAll('.chat-item');

        // Снимаем активный класс со всех чатов
        chatItems.forEach(item => {
            item.classList.remove('active');
        });

        // Если есть текущий чат в URL, отмечаем его как активный
        if (currentChatId && currentChatId !== '/' && currentChatId !== 'new-chat') {
            chatItems.forEach(item => {
                const chatId = item.getAttribute('data-chat-id');
                if (chatId == currentChatId) {
                    item.classList.add('active');
                    console.log('Marked chat as active:', chatId);
                }
            });
        }
    }

    toggleSidebar() {
        const isOpening = !this.sidebar.classList.contains('open');

        this.sidebar.classList.toggle('open');
        this.sidebarOverlay.classList.toggle('open');

        // Блокируем скролл body когда сайдбар открыт
        if (isOpening) {
            document.body.classList.add('sidebar-open');
            document.body.style.overflow = 'hidden';
        } else {
            document.body.classList.remove('sidebar-open');
            document.body.style.overflow = '';
        }
    }

    closeSidebar() {
        this.sidebar.classList.remove('open');
        this.sidebarOverlay.classList.remove('open');
        document.body.classList.remove('sidebar-open');
        document.body.style.overflow = '';
    }

    bindChatItems() {
        const chatItems = document.querySelectorAll('.chat-item');
        console.log('Found chat items:', chatItems.length);

        chatItems.forEach(item => {
            const chatId = item.getAttribute('data-chat-id');

            item.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Chat item clicked:', chatId);
                this.selectChat(item);
                this.closeSidebar();
            });
        });

        // Закрываем сайдбар при клике на кнопки пользователя
        const userButtons = document.querySelectorAll('.user-actions-sidebar button, .user-actions-sidebar a');
        userButtons.forEach(button => {
            button.addEventListener('click', () => {
                setTimeout(() => {
                    this.closeSidebar();
                }, 100);
            });
        });
    }

    selectChat(chatItem) {
        const chatId = chatItem.getAttribute('data-chat-id');
        console.log('Selected chat:', chatId);

        // Обновляем текущий ID чата в менеджере
        this.chatManager.setCurrentChatId(chatId);

        // Обновляем URL без перезагрузки страницы
        const newUrl = '/' + chatId;
        history.pushState({ chatId }, '', newUrl);
        console.log('URL updated to:', newUrl);

        // Обновляем активный чат в UI
        this.updateActiveChat(chatItem);

        // Загружаем историю выбранного чата
        this.chatManager.loadChatHistory(chatId);

        // На мобильных устройствах закрываем меню после выбора
        if (window.innerWidth <= 768) {
            this.closeSidebar();
        }
    }

    updateActiveChat(activeChatItem) {
        const chatItems = document.querySelectorAll('.chat-item');
        chatItems.forEach(chat => {
            chat.classList.remove('active');
            console.log('Removed active class from:', chat.getAttribute('data-chat-id'));
        });

        activeChatItem.classList.add('active');
        console.log('Added active class to:', activeChatItem.getAttribute('data-chat-id'));
    }

    createNewChat() {
        console.log('Создание нового чата в UI');

        // Временный ID для UI (будет заменен на серверный при первом сообщении)
        const tempChatId = 'new-chat';

        // Обновляем URL на новый чат
        const newUrl = '/' + tempChatId;
        history.pushState({ chatId: tempChatId }, '', newUrl);

        // Показываем пустое состояние для нового чата
        this.chatManager.showEmptyState();
        this.chatManager.setCurrentChatId(tempChatId);

        console.log('Новый чат создан в UI с временным ID:', tempChatId);

        // Обновляем активные чаты в сайдбаре
        this.markCurrentChat();

        return tempChatId;
    }

    updateTempChatToServerChat(tempChatId, serverChatId, chatName = 'Новый чат') {
        console.log('Updating temp chat:', tempChatId, 'to server chat:', serverChatId, 'with name:', chatName);

        const chatList = document.getElementById('chat-list');
        if (!chatList) {
            console.error('Chat list not found');
            return;
        }

        // Создаем новый элемент чата
        const newChatItem = document.createElement('li');
        newChatItem.className = 'chat-item active';
        newChatItem.setAttribute('data-chat-id', serverChatId);
        newChatItem.innerHTML = `
            <div class="chat-name">${chatName}</div>
            <div class="chat-preview">Чат создан</div>
        `;

        // Добавляем обработчик события для нового чата
        newChatItem.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.selectChat(newChatItem);
        });

        // Добавляем новый чат в начало списка
        chatList.insertBefore(newChatItem, chatList.firstChild);

        // Обновляем UI
        this.updateActiveChat(newChatItem);

        // Обновляем URL
        const newUrl = '/' + serverChatId;
        history.replaceState({ chatId: serverChatId }, '', newUrl);

        console.log('Temp chat updated to server chat:', serverChatId);

        // Перепривязываем обработчики
        this.bindChatItems();
    }
}
