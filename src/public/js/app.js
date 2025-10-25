import { Sidebar } from './components/sidebar.js';
import { InputHandler } from './components/input.js';
import { ChatManager } from './components/chat.js';

class App {
    constructor() {
        this.chatManager = null;
        this.inputHandler = null;
        this.sidebar = null;
        this.init();
    }

    init() {
        // Ждем загрузки DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initializeApp());
        } else {
            this.initializeApp();
        }
    }

    initializeApp() {
        try {
            // Инициализируем менеджер чата
            this.chatManager = new ChatManager();

            // Инициализируем обработчик ввода
            this.inputHandler = new InputHandler(this.chatManager);

            // Инициализируем сайдбар
            this.sidebar = new Sidebar(this.chatManager);

            // Делаем доступным глобально для отладки
            window.app = this;

            console.log('App initialized successfully');
        } catch (error) {
            console.error('Error initializing app:', error);
        }
    }
}

// Запускаем приложение
new App();
