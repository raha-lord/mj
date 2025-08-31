import './bootstrap';
import { createApp } from 'vue';
import Antd from 'ant-design-vue';
import 'ant-design-vue/dist/reset.css';

import TaskManager from './Components/TaskManager.vue';
import './utils/themeManager.js';

document.addEventListener('DOMContentLoaded', () => {
    const taskManagerApp = document.getElementById('task-manager-app');
    if (taskManagerApp) {
        console.log('Initializing Vue app...');
        
        const app = createApp(TaskManager, {
            initialData: window.taskManagerInitialData || {}
        });
        
        app.use(Antd);
        
        app.config.errorHandler = (err, instance, info) => {
            console.error('Vue error:', err, instance, info);
        };
        
        app.mount('#task-manager-app');
        console.log('TaskManager mounted successfully');
    }
});