// Менеджер тем
class ThemeManager {
    constructor() {
        this.currentTheme = localStorage.getItem('theme') || 'green';
        this.themeLink = null;
        this.init();
    }

    init() {
        // Создаем или находим link элемент для темы
        this.themeLink = document.getElementById('theme-css');
        if (!this.themeLink) {
            this.themeLink = document.createElement('link');
            this.themeLink.id = 'theme-css';
            this.themeLink.rel = 'stylesheet';
            document.head.appendChild(this.themeLink);
        }
        
        // Применяем сохраненную тему
        this.setTheme(this.currentTheme);
    }

    setTheme(themeName) {
        const themes = {
            'green': '/css/themes/green.css',
            'blue': '/css/themes/blue.css', 
            'purple': '/css/themes/purple.css'
        };

        if (!themes[themeName]) {
            console.error('Theme not found:', themeName);
            return;
        }

        this.currentTheme = themeName;
        this.themeLink.href = themes[themeName];
        
        // Сохраняем в localStorage
        localStorage.setItem('theme', themeName);
        
        // Добавляем атрибут к body для CSS переменных
        document.body.setAttribute('data-theme', themeName);
        
        console.log('Theme changed to:', themeName);
        
        // Эмитим событие для реакции других компонентов
        window.dispatchEvent(new CustomEvent('theme-changed', { 
            detail: { theme: themeName } 
        }));
    }

    getTheme() {
        return this.currentTheme;
    }

    getAvailableThemes() {
        return [
            { value: 'green', label: 'Зеленая', color: '#52c41a' },
            { value: 'blue', label: 'Синяя', color: '#1890ff' },
            { value: 'purple', label: 'Фиолетовая', color: '#722ed1' }
        ];
    }
}

// Создаем глобальный экземпляр
window.themeManager = new ThemeManager();

export default window.themeManager;