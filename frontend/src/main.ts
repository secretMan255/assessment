import { createApp } from 'vue'
import { createPinia } from 'pinia'

import './style.css'

import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import '@mdi/font/css/materialdesignicons.css'
import { aliases, mdi } from 'vuetify/iconsets/mdi'

import App from './App.vue'
import router from './router'

const vuetify = createVuetify({
    components,
    directives,
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: { mdi },
    },
})

createApp(App)
    .use(createPinia())
    .use(router)
    .use(vuetify)
    .mount('#app')
