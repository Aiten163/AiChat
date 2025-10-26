export class Sidebar {
    constructor(chatManager) {
        this.chatManager = chatManager;
        this.sidebar = document.getElementById('sidebar');
        this.container = document.getElementById('container');
        this.sidebarOverlay = document.getElementById('sidebar-overlay');
        this.menuToggle = document.getElementById('menu-toggle');
        this.newChatBtn = document.getElementById('new-chat-btn');
        this.isEditing = false;
        this.currentEditChatId = null;
        this.pendingDeleteChatId = null; // ID чата для удаления

        this.init();
    }

    init() {
        this.bindEvents();
        this.markCurrentChat();
        this.bindChatActions();
        this.bindModalEvents(); // Инициализируем модалку
    }

    bindEvents() {
        // Открытие/закрытие меню
        if (this.menuToggle) {
            this.menuToggle.addEventListener('click', () => this.toggleSidebar());
        }

        // Кнопка нового чата
        if (this.newChatBtn) {
            this.newChatBtn.addEventListener('click', () => this.createNewChat());
        }

        // Закрытие меню при клике на оверлей
        if (this.sidebarOverlay) {
            this.sidebarOverlay.addEventListener('click', () => this.closeSidebar());
        }

        this.bindChatItems();

        window.addEventListener('popstate', () => {
            const newChatId = this.chatManager.getCurrentChatIdFromURL();
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

    bindModalEvents() {
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const deleteChatModal = document.getElementById('deleteChatModal');

        if (confirmDeleteBtn && deleteChatModal) {
            confirmDeleteBtn.addEventListener('click', () => {
                this.confirmDeleteChat();
            });

            // Сбрасываем pending chat ID при закрытии модалки
            deleteChatModal.addEventListener('hidden.bs.modal', () => {
                this.pendingDeleteChatId = null;
            });
        }
    }

    bindChatActions() {
        const renameButtons = document.querySelectorAll('.chat-action-btn.rename');
        renameButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const chatId = button.getAttribute('data-chat-id');
                this.startRenamingChat(chatId);
            });
        });

        const deleteButtons = document.querySelectorAll('.chat-action-btn.delete');
        deleteButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const chatId = button.getAttribute('data-chat-id');
                this.deleteChat(chatId);
            });
        });

        document.addEventListener('click', (e) => {
            if (this.isEditing && !e.target.closest('.chat-item.editing')) {
                this.finishRenamingChat();
            }
        });
    }

    startRenamingChat(chatId) {
        if (this.isEditing) {
            this.cancelRenamingChat();
            return;
        }

        this.isEditing = true;
        this.currentEditChatId = chatId;

        const chatItem = document.querySelector(`.chat-item[data-chat-id="${chatId}"]`);
        if (!chatItem) return;

        // Снимаем активный класс со всех чатов
        document.querySelectorAll('.chat-item').forEach(item => {
            item.classList.remove('active');
        });

        chatItem.classList.add('editing', 'active');

        const input = chatItem.querySelector('.chat-name-edit');
        const currentName = chatItem.querySelector('.chat-name').textContent;

        input.value = currentName;
        input.style.display = 'block';
        input.focus();
        input.select();

        const handleKeydown = (e) => {
            if (e.key === 'Enter') {
                this.finishRenamingChat();
            } else if (e.key === 'Escape') {
                this.cancelRenamingChat();
            }
        };

        const handleBlur = () => {
            setTimeout(() => {
                if (this.isEditing) {
                    this.finishRenamingChat();
                }
            }, 150);
        };

        input.addEventListener('keydown', handleKeydown);
        input.addEventListener('blur', handleBlur);

        input._renameHandlers = { handleKeydown, handleBlur };
    }

    async finishRenamingChat() {
        if (!this.isEditing || !this.currentEditChatId) return;

        const chatItem = document.querySelector(`.chat-item[data-chat-id="${this.currentEditChatId}"]`);
        if (!chatItem) return;

        const input = chatItem.querySelector('.chat-name-edit');
        const newName = input.value.trim();
        const oldName = chatItem.querySelector('.chat-name').textContent;

        if (!newName) {
            this.cancelRenamingChat();
            return;
        }

        if (newName === oldName) {
            this.cancelRenamingChat();
            return;
        }

        try {
            const response = await fetch(`/chats/${this.currentEditChatId}/rename`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.chatManager.getCsrfToken()
                },
                body: JSON.stringify({ name: newName })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                chatItem.querySelector('.chat-name').textContent = result.new_name;
                this.cancelRenamingChat();
            }

        } catch (error) {
            console.error('Ошибка переименования чата:', error);
            this.cancelRenamingChat();
        }
    }

    async finishRenamingChat() {
        if (!this.isEditing || !this.currentEditChatId) return;

        const chatItem = document.querySelector(`.chat-item[data-chat-id="${this.currentEditChatId}"]`);
        if (!chatItem) return;

        const input = chatItem.querySelector('.chat-name-edit');
        const newName = input.value.trim();
        const oldName = chatItem.querySelector('.chat-name').textContent;

        if (!newName) {
            this.cancelRenamingChat();
            return;
        }

        if (newName === oldName) {
            this.cancelRenamingChat();
            return;
        }

        try {
            console.log('Sending rename request for chat:', this.currentEditChatId);

            const response = await fetch(`/chats/${this.currentEditChatId}/rename`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.chatManager.getCsrfToken()
                },
                body: JSON.stringify({
                    name: newName
                })
            });

            console.log('Rename response status:', response.status);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Rename error response:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log('Rename result:', result);

            if (result.success) {
                chatItem.querySelector('.chat-name').textContent = result.new_name;
                this.cancelRenamingChat();
            } else {
                throw new Error(result.error || 'Unknown error');
            }

        } catch (error) {
            console.error('Ошибка переименования чата:', error);
            this.cancelRenamingChat();
        }
    }

    cancelRenamingChat() {
        if (!this.isEditing || !this.currentEditChatId) return;

        const chatItem = document.querySelector(`.chat-item[data-chat-id="${this.currentEditChatId}"]`);
        if (chatItem) {
            chatItem.classList.remove('editing');
            const input = chatItem.querySelector('.chat-name-edit');
            input.style.display = 'none';
            input.value = chatItem.querySelector('.chat-name').textContent;

            if (input._renameHandlers) {
                input.removeEventListener('keydown', input._renameHandlers.handleKeydown);
                input.removeEventListener('blur', input._renameHandlers.handleBlur);
                delete input._renameHandlers;
            }
        }

        this.isEditing = false;
        this.currentEditChatId = null;
    }

    showDeleteConfirmationModal() {
        const deleteChatModal = new bootstrap.Modal(document.getElementById('deleteChatModal'));
        deleteChatModal.show();
    }

    async deleteChat(chatId) {
        if (this.isEditing) {
            this.cancelRenamingChat();
            return;
        }

        if (!confirm('Вы уверены, что хотите удалить этот чат? Все сообщения будут удалены.')) {
            return;
        }

        try {
            console.log('Sending delete request for chat:', chatId);

            const response = await fetch(`/chats/${chatId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.chatManager.getCsrfToken()
                }
            });

            console.log('Delete response status:', response.status);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Delete error response:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log('Delete result:', result);

            if (result.success) {
                const chatItem = document.querySelector(`.chat-item[data-chat-id="${chatId}"]`);
                if (chatItem) {
                    chatItem.remove();
                }

                if (this.chatManager.getCurrentChatId() == chatId) {
                    window.location.href = '/';
                }
            }

        } catch (error) {
            console.error('Ошибка удаления чата:', error);
        }
    }
    async confirmDeleteChat() {
        if (!this.pendingDeleteChatId) return;

        const chatId = this.pendingDeleteChatId;

        try {
            const response = await fetch(`/chats/${chatId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.chatManager.getCsrfToken()
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                // Закрываем модалку
                const deleteChatModal = bootstrap.Modal.getInstance(document.getElementById('deleteChatModal'));
                deleteChatModal.hide();

                // Удаляем чат из UI
                const chatItem = document.querySelector(`.chat-item[data-chat-id="${chatId}"]`);
                if (chatItem) {
                    chatItem.remove();
                }

                // Если удаленный чат был активным, переходим на главную
                if (this.chatManager.getCurrentChatId() == chatId) {
                    window.location.href = '/';
                }

                // Показываем уведомление об успешном удалении
                this.showSuccessNotification('Чат успешно удален');
            }

        } catch (error) {
            console.error('Ошибка удаления чата:', error);
            this.showErrorNotification('Ошибка при удалении чата');

            // Закрываем модалку в случае ошибки
            const deleteChatModal = bootstrap.Modal.getInstance(document.getElementById('deleteChatModal'));
            deleteChatModal.hide();
        }

        this.pendingDeleteChatId = null;
    }

    // Показываем уведомление об успехе
    showSuccessNotification(message) {
        this.showNotification(message, 'success');
    }

    // Показываем уведомление об ошибке
    showErrorNotification(message) {
        this.showNotification(message, 'danger');
    }

    // Универсальный метод для показа уведомлений
    showNotification(message, type = 'info') {
        // Создаем элемент уведомления
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 100px;
            right: 20px;
            z-index: 1080;
            min-width: 300px;
        `;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        `;

        // Добавляем в body
        document.body.appendChild(notification);

        // Автоматически скрываем через 5 секунд
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    markCurrentChat() {
        const currentChatId = this.chatManager.getCurrentChatIdFromURL();
        console.log('Marking current chat:', currentChatId);

        const chatItems = document.querySelectorAll('.chat-item');
        chatItems.forEach(item => {
            item.classList.remove('active');
        });

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
        if (this.isEditing) {
            this.cancelRenamingChat();
        }

        const isOpening = !this.sidebar.classList.contains('open');
        this.sidebar.classList.toggle('open');
        this.sidebarOverlay.classList.toggle('open');

        if (isOpening) {
            document.body.classList.add('sidebar-open');
            document.body.style.overflow = 'hidden';
        } else {
            document.body.classList.remove('sidebar-open');
            document.body.style.overflow = '';
        }
    }

    closeSidebar() {
        if (this.isEditing) {
            this.finishRenamingChat();
        }

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
                if (e.target.closest('.chat-actions') || e.target.classList.contains('chat-name-edit') || this.isEditing) {
                    return;
                }

                e.preventDefault();
                e.stopPropagation();
                console.log('Chat item clicked:', chatId);
                this.selectChat(item);
                this.closeSidebar();
            });
        });

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

        this.chatManager.setCurrentChatId(chatId);
        const newUrl = '/' + chatId;
        history.pushState({ chatId }, '', newUrl);
        console.log('URL updated to:', newUrl);

        this.updateActiveChat(chatItem);
        this.chatManager.loadChatHistory(chatId);

        if (window.innerWidth <= 768) {
            this.closeSidebar();
        }
    }

    updateActiveChat(activeChatItem) {
        const chatItems = document.querySelectorAll('.chat-item');
        chatItems.forEach(chat => {
            chat.classList.remove('active');
        });
        activeChatItem.classList.add('active');
    }

    createNewChat() {
        if (this.isEditing) {
            this.cancelRenamingChat();
            return;
        }

        console.log('Создание нового чата в UI');
        const tempChatId = 'new-chat';
        const newUrl = '/' + tempChatId;
        history.pushState({ chatId: tempChatId }, '', newUrl);

        this.chatManager.showEmptyState();
        this.chatManager.setCurrentChatId(tempChatId);
        console.log('Новый чат создан в UI с временным ID:', tempChatId);

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

        const newChatItem = document.createElement('li');
        newChatItem.className = 'chat-item active';
        newChatItem.setAttribute('data-chat-id', serverChatId);
        newChatItem.innerHTML = `
            <div class="chat-name-container">
                <span class="chat-name">${chatName}</span>
                <input type="text" class="chat-name-edit" value="${chatName}" style="display: none;">
            </div>
            <div class="chat-preview">Чат создан</div>
            <div class="chat-actions">
                <button class="chat-action-btn rename" title="Переименовать чат" data-chat-id="${serverChatId}">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="chat-action-btn delete" title="Удалить чат" data-chat-id="${serverChatId}">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

        newChatItem.addEventListener('click', (e) => {
            if (e.target.closest('.chat-actions') || e.target.classList.contains('chat-name-edit') || this.isEditing) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            this.selectChat(newChatItem);
        });

        chatList.insertBefore(newChatItem, chatList.firstChild);
        this.bindChatActions();
        this.updateActiveChat(newChatItem);

        const newUrl = '/' + serverChatId;
        history.replaceState({ chatId: serverChatId }, '', newUrl);
        console.log('Temp chat updated to server chat:', serverChatId);
    }
}
