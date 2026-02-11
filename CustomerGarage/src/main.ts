import './assets/main.css'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

// Import cache debug tools (only active in dev mode)
import './utils/cacheDebug'

createApp(App).use(router).mount('#app')
