import axios from 'axios'
import router from '@/router'
import { useAuthStore } from '@/stores/auth'

const http = axios.create({
    baseURL: import.meta.env.VITE_API_BASE || 'http://localhost:8000/api',
})

http.interceptors.request.use((config) => {
    const auth = useAuthStore()
    const token = auth.token || localStorage.getItem('token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
})

http.interceptors.response.use(
    (res) => res,
    (err) => {
        if (err?.response?.status === 401) {
            const auth = useAuthStore()
            auth.logout()
            router.push({ name: 'login' })
        }
        return Promise.reject(err)
    }
)

export default http