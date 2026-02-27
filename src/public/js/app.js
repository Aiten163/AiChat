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
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initializeApp());
        } else {
            this.initializeApp();
        }
    }

    initializeApp() {
        try {
            this.chatManager = new ChatManager();

            this.inputHandler = new InputHandler(this.chatManager);

            this.sidebar = new Sidebar(this.chatManager);

            window.app = this;

        } catch (error) {
        }
    }
}

new App();
