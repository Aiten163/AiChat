import { Sidebar } from './components/sidebar.js';
import { InputHandler } from './components/input.js';
import { ChatManager } from './components/chat.js';

class App {
    constructor() {
        this.sidebar = null;
        this.inputHandler = null;
        this.chatManager = null;
        this.init();
    }

    init() {
        // Инициализация компонентов
        this.chatManager = new ChatManager();
        this.sidebar = new Sidebar(this.chatManager);
        this.inputHandler = new InputHandler(this.chatManager);

        // Делаем глобально доступными часто используемые функции
        window.createNewChat = () => this.sidebar.createNewChat();
        window.loadChatHistory = (chatId) => this.chatManager.loadChatHistory(chatId);

        // Для обратной совместимости с inline обработчиками
        window.autoResize = (textarea) => InputHandler.autoResize(textarea);
        window.sendMessage = () => InputHandler.sendMessage();

        console.log('App initialized');
    }
}

// Инициализация приложения когда DOM готов
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.app = new App();
    });
} else {
    window.app = new App();
}
