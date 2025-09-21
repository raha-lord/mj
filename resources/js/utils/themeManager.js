// Менеджер тем
class ThemeManager {
    constructor() {
        this.currentTheme = localStorage.getItem('theme') || 'light';
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
            'light': '/css/themes/light.css',
            'dark': '/css/themes/dark.css'
        };

        if (!themes[themeName]) {
            console.error('Theme not found:', themeName);
            return;
        }

        this.currentTheme = themeName;
        this.themeLink.href = themes[themeName];
        
        // Сохраняем в localStorage
        localStorage.setItem('theme', themeName);
        
        // Добавляем атрибут к body для CSS переменных и дополнительных стилей
        document.body.setAttribute('data-theme', themeName);
        
        // Добавляем CSS класс для Tailwind dark: модификаторов
        if (themeName === 'dark') {
            document.documentElement.classList.add('dark');
            document.body.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
            document.body.classList.remove('dark');
        }
        
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
            { 
                value: 'light', 
                label: 'Светлая', 
                color: '#ffffff',
                icon: '☀️'
            },
            { 
                value: 'dark', 
                label: 'Темная', 
                color: '#1f1f1f',
                icon: '🌙'
            }
        ];
    }

    isDarkTheme() {
        return this.currentTheme === 'dark';
    }

    isLightTheme() {
        return this.currentTheme === 'light';
    }

    toggleTheme() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.setTheme(newTheme);
        return newTheme;
    }
}

// Создаем глобальный экземпляр
window.themeManager = new ThemeManager();

export default window.themeManager;