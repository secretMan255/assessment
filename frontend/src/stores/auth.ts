import http from '@/api/http';
import { defineStore } from 'pinia'


type User = { id: number; name: string; email: string } | null

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: (localStorage.getItem('token') || '') as string,
        user: null as User,
        loading: false,
        error: '' as string,
    }),
    getters: {
        isAuthed: (s) => !!s.token,
    },
    actions: {
        async login(email: string, password: string) {
            this.loading = true
            this.error = ''
            try {
                const { data } = await http.post('/login', { email, password })

                const tk = data?.access_token || data?.token || data?.data?.accessToken
                if (!tk) throw new Error('No token in response')
                this.token = tk
                localStorage.setItem('token', tk)
            } catch (e: any) {
                this.error = e?.message || 'Login failed'
                throw e
            } finally {
                this.loading = false
            }
        },
        logout() {
            this.token = ''
            this.user = null
            localStorage.removeItem('token')
        },
    },
})