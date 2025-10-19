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
        this.sidebar.classList.toggle('open');
        if (this.container) this.container.classList.toggle('sidebar-open');
        this.sidebarOverlay.classList.toggle('open');
    }

    closeSidebar() {
        this.sidebar.classList.remove('open');
        if (this.container) this.container.classList.remove('sidebar-open');
        this.sidebarOverlay.classList.remove('open');
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

    // Функция создания нового чата
    createNewChat() {
        console.log('Создание нового чата в UI');

        const chatList = document.getElementById('chat-list');
        if (!chatList) {
            console.error('Chat list not found');
            return;
        }

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

    updateTempChatToServerChat(tempChatId, serverChatId, chatName = 'Новый чат') {
        console.log('Updating temp chat:', tempChatId, 'to server chat:', serverChatId, 'with name:', chatName);

        const chatItems = document.querySelectorAll('.chat-item');
        let tempChatItem = null;

        // Находим временный чат
        chatItems.forEach(item => {
            if (item.getAttribute('data-chat-id') === tempChatId) {
                tempChatItem = item;
            }
        });

        if (tempChatItem) {
            // Обновляем ID и название
            tempChatItem.setAttribute('data-chat-id', serverChatId);
            tempChatItem.querySelector('.chat-name').textContent = chatName;
            tempChatItem.querySelector('.chat-preview').textContent = 'Чат создан';

            console.log('Temp chat updated to server chat:', serverChatId);

            // Если этот чат активен, обновляем URL
            if (tempChatItem.classList.contains('active')) {
                const newUrl = '/' + serverChatId;
                history.replaceState({ chatId: serverChatId }, '', newUrl);
                this.chatManager.setCurrentChatId(serverChatId);
                console.log('URL updated to server chat ID:', serverChatId);
            }
        }
    }
}
