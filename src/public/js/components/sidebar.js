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
        this.pendingDeleteChatId = null;

        this.init();
    }

    init() {
        this.bindEvents();
        this.markCurrentChat();
        this.bindChatActions();
        this.bindModalEvents();
        this.bindSupportModalEvents();
    }

    bindEvents() {
        if (this.menuToggle) {
            this.menuToggle.addEventListener('click', () => this.toggleSidebar());
        }

        if (this.newChatBtn) {
            this.newChatBtn.addEventListener('click', () => this.createNewChat());
        }

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
    bindSupportModalEvents() {
        const sendSupportBtn = document.getElementById('sendSupportBtn');
        if (sendSupportBtn) {
            sendSupportBtn.addEventListener('click', () => this.sendSupportMessage());
        }

        const reportModal = document.getElementById('reportModal');
        if (reportModal) {
            reportModal.addEventListener('hidden.bs.modal', () => {
                this.clearSupportForm();
            });
        }

        const attachImageIcon = document.getElementById('attachImageIcon');
        if (attachImageIcon) {
            attachImageIcon.addEventListener('click', () => this.triggerImageInput());
        }

        const imageInput = document.getElementById('imageInput');
        if (imageInput) {
            imageInput.addEventListener('change', (e) => this.handleImageSelect(e));
        }
    }
    triggerImageInput() {
        const imageInput = document.getElementById('imageInput');
        if (imageInput) {
            imageInput.click();
        }
    }
    clearSupportForm() {
        const form = document.getElementById('supportForm');
        if (form) {
            form.reset();
        }

        const previewContainer = document.getElementById('imagePreviewContainer');
        if (previewContainer) {
            previewContainer.remove();
        }
    }
    async sendSupportMessage() {
        const message = document.getElementById('messageText');
        const imageInput = document.getElementById('imageInput');
        const imageFile = imageInput.files[0];

        if (!message.value.trim()) {
            this.showErrorNotification('Пожалуйста, введите сообщение');
            return;
        }

        this.setSupportButtonState('loading');

        try {
            const formData = new FormData();
            formData.append('message', message.value.trim());
            formData.append('_token', this.chatManager.getCsrfToken());

            if (imageFile) {
                formData.append('image', imageFile);
            }

            const response = await fetch('/api/support', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const contentType = response.headers.get('content-type');

            if (!contentType || !contentType.includes('application/json')) {
                const textResponse = await response.text();
                console.error('Server returned non-JSON response:', textResponse.substring(0, 200));

                if (response.status === 404) {
                    throw new Error('Сервер не найден. Проверьте URL адрес.');
                } else if (response.status === 419) {
                    throw new Error('Сессия истекла. Пожалуйста, обновите страницу.');
                } else if (response.status === 500) {
                    throw new Error('Внутренняя ошибка сервера. Пожалуйста, попробуйте позже.');
                } else {
                    throw new Error(`Сервер вернул ошибку: ${response.status}`);
                }
            }

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.error || `Ошибка сервера: ${response.status}`);
            }

            if (result.success) {
                this.showSuccessNotification(result.message || 'Сообщение отправлено в техподдержку!');

                const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
                if (modal) {
                    modal.hide();
                }

                this.clearSupportForm();
            } else {
                throw new Error(result.error || 'Ошибка при отправке сообщения');
            }

        } catch (error) {
            console.error('Ошибка отправки сообщения:', error);

            if (error.name === 'SyntaxError') {
                this.showErrorNotification('Ошибка формата ответа от сервера. Пожалуйста, попробуйте позже.');
            } else {
                this.showErrorNotification('Ошибка при отправке сообщения: ' + error.message);
            }
        } finally {
            this.setSupportButtonState('normal');
        }
    }
    handleImageSelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            this.showErrorNotification('Пожалуйста, выберите файл изображения (JPG, PNG, GIF)');
            this.clearImageInput();
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            this.showErrorNotification('Размер файла не должен превышать 5MB');
            this.clearImageInput();
            return;
        }

        this.showImagePreview(file);
    }
    clearImageInput() {
        const imageInput = document.getElementById('imageInput');
        if (imageInput) {
            imageInput.value = '';
        }
    }
    showImagePreview(file) {
        const reader = new FileReader();

        reader.onload = (e) => {
            let previewContainer = document.getElementById('imagePreviewContainer');
            if (!previewContainer) {
                previewContainer = document.createElement('div');
                previewContainer.id = 'imagePreviewContainer';
                previewContainer.className = 'image-preview-container mt-3';

                const messageInput = document.getElementById('messageText');
                messageInput.parentNode.appendChild(previewContainer);
            }

            previewContainer.innerHTML = `
                <div class="image-preview-card">
                    <div class="preview-header">
                        <span><i class="bi bi-image me-2"></i>Прикрепленное изображение</span>
                        <button type="button" class="btn-close btn-close-white" onclick="this.closest('.image-preview-container').remove(); document.getElementById('imageInput').value = '';"></button>
                    </div>
                    <div class="preview-body">
                        <img src="${e.target.result}" alt="Превью" class="preview-image">
                        <div class="preview-info">
                            <small class="text-muted">${file.name} (${this.formatFileSize(file.size)})</small>
                        </div>
                    </div>
                </div>
            `;
        };

        reader.readAsDataURL(file);
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    async uploadImage() {
        const imageInput = document.getElementById('imageInput');
        const file = imageInput.files[0];

        if (!file) {
            this.showErrorNotification('Пожалуйста, выберите изображение');
            return;
        }

        this.setUploadButtonState('loading');

        try {
            const formData = new FormData();
            formData.append('image', file);
            formData.append('_token', this.chatManager.getCsrfToken());

            const response = await fetch('/api/upload-image', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                this.showSuccessNotification('Изображение успешно загружено!');
                this.clearImageForm();

                if (result.imageUrl) {
                    this.handleUploadedImage(result.imageUrl);
                }
            } else {
                throw new Error(result.error || 'Ошибка при загрузке изображения');
            }

        } catch (error) {
            console.error('Ошибка загрузки изображения:', error);
            this.showErrorNotification('Ошибка при загрузке изображения: ' + error.message);
        } finally {
            this.setUploadButtonState('normal');
        }
    }
    setUploadButtonState(state) {
        const uploadBtn = document.getElementById('uploadImageBtn');
        if (!uploadBtn) return;

        if (state === 'loading') {
            uploadBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin me-2"></i>Загрузка...';
            uploadBtn.disabled = true;
        } else {
            uploadBtn.innerHTML = '<i class="bi bi-cloud-upload me-2"></i>Загрузить изображение';
            uploadBtn.disabled = false;
        }
    }
    handleUploadedImage(imageUrl) {
        console.log('Изображение загружено:', imageUrl);

        if (this.chatManager && this.chatManager.getCurrentChatId()) {
        }
    }
    clearImageForm() {
        const imageInput = document.getElementById('imageInput');
        if (imageInput) {
            imageInput.value = '';
        }

        const previewContainer = document.getElementById('imagePreviewContainer');
        if (previewContainer) {
            previewContainer.remove();
        }
    }
    setSupportButtonState(state) {
        const sendBtn = document.getElementById('sendSupportBtn');
        if (!sendBtn) return;

        if (state === 'loading') {
            sendBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin me-2"></i>Отправка...';
            sendBtn.disabled = true;
        } else {
            sendBtn.innerHTML = '<i class="bi bi-send me-2"></i>Отправить';
            sendBtn.disabled = false;
        }
    }

    bindModalEvents() {
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const deleteChatModal = document.getElementById('deleteChatModal');

        if (confirmDeleteBtn && deleteChatModal) {
            confirmDeleteBtn.addEventListener('click', () => {
                this.confirmDeleteChat();
            });
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


            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                chatItem.querySelector('.chat-name').textContent = result.new_name;
                this.cancelRenamingChat();
            } else {
                throw new Error(result.error || 'Unknown error');
            }

        } catch (error) {
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

            const response = await fetch(`/chats/${chatId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.chatManager.getCsrfToken()
                }
            });


            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

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

                const chatItem = document.querySelector(`.chat-item[data-chat-id="${chatId}"]`);
                if (chatItem) {
                    chatItem.remove();
                }

                if (this.chatManager.getCurrentChatId() == chatId) {
                    window.location.href = '/';
                }

                this.showSuccessNotification('Чат успешно удален');
            }

        } catch (error) {
            this.showErrorNotification('Ошибка при удалении чата');

            const deleteChatModal = bootstrap.Modal.getInstance(document.getElementById('deleteChatModal'));
            deleteChatModal.hide();
        }

        this.pendingDeleteChatId = null;
    }

    showSuccessNotification(message) {
        this.showNotification(message, 'success');
    }

    showErrorNotification(message) {
        this.showNotification(message, 'danger');
    }

    showNotification(message, type = 'info') {
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

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    markCurrentChat() {
        const currentChatId = this.chatManager.getCurrentChatIdFromURL();

        const chatItems = document.querySelectorAll('.chat-item');
        chatItems.forEach(item => {
            item.classList.remove('active');
        });

        if (currentChatId && currentChatId !== '/' && currentChatId !== 'new-chat') {
            chatItems.forEach(item => {
                const chatId = item.getAttribute('data-chat-id');
                if (chatId == currentChatId) {
                    item.classList.add('active');
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
        chatItems.forEach(item => {
            const chatId = item.getAttribute('data-chat-id');

            item.addEventListener('click', (e) => {
                if (e.target.closest('.chat-actions') || e.target.classList.contains('chat-name-edit') || this.isEditing) {
                    return;
                }

                e.preventDefault();
                e.stopPropagation();
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

        this.chatManager.setCurrentChatId(chatId);
        const newUrl = '/' + chatId;
        history.pushState({ chatId }, '', newUrl);

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

        const tempChatId = 'new-chat';
        const newUrl = '/' + tempChatId;
        history.pushState({ chatId: tempChatId }, '', newUrl);

        this.chatManager.showEmptyState();
        this.chatManager.setCurrentChatId(tempChatId);

        this.markCurrentChat();
        return tempChatId;
    }

    updateTempChatToServerChat(tempChatId, serverChatId, chatName = 'Новый чат') {

        const chatList = document.getElementById('chat-list');
        if (!chatList) {

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
    }
}
