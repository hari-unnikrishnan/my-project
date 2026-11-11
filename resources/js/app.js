import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

const sidebar = document.querySelector('.sidebar');
const sidebarToggle = document.querySelector('.sidebar-toggle');
const themeToggle = document.querySelector('.theme-toggle');
const promptInput = document.querySelector('#main-prompt');
const sendButton = document.querySelector('#send-prompt');
const chatForm = document.querySelector('#chat-form');

if (sidebar && sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
    });
}

if (themeToggle) {
    const applyTheme = (theme) => {
        document.body.setAttribute('data-theme', theme);
        themeToggle.innerHTML = theme === 'dark'
            ? '<i class="bi bi-sun"></i>'
            : '<i class="bi bi-moon-stars"></i>';
    };

    const storedTheme = localStorage.getItem('aether-theme') || 'dark';
    applyTheme(storedTheme);

    themeToggle.addEventListener('click', () => {
        const theme = document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem('aether-theme', theme);
        applyTheme(theme);
    });
}

if (promptInput && sendButton) {
    sendButton.addEventListener('click', () => {
        const value = promptInput.value.trim();
        if (!value) return;
        promptInput.value = '';
    });
}

if (chatForm) {
    chatForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const textarea = chatForm.querySelector('textarea');
        if (!textarea || !textarea.value.trim()) return;
        const bubble = document.createElement('div');
        bubble.className = 'message-row user';
        bubble.innerHTML = '<div class="message-bubble">' + textarea.value.trim() + '</div>';
        const container = document.querySelector('.chat-window');
        if (container) container.appendChild(bubble);
        textarea.value = '';
    });
}
