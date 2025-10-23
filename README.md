# Notes App (Vue 3 + Vuetify + Laravel API)

A small notes application built with **Vue 3 + Vite + TypeScript + Vuetify** on the frontend and a **Laravel API (Sanctum)** on the backend.

- Server‑side **pagination / search / sort**
- Loading indicators & toast notifications
- Dockerized dev setup (frontend + backend)

---

## Table of contents
- [Requirements](#requirements)
- [Project layout](#project-layout)
- [Run with Docker (recommended)](#run-with-docker-recommended)
  - [Environment variables (Docker)](#environment-variables-docker)
  - [Start the stack](#start-the-stack)
  - [What you get](#what-you-get)
- [Run locally without Docker](#run-locally-without-docker)
  - [Backend (Laravel)](#backend-laravel)
  - [Frontend (Vue 3 + Vite)](#frontend-vue-3--vite)
- [Environment variable guide](#environment-variable-guide)
- [Useful scripts](#useful-scripts)
- [Troubleshooting](#troubleshooting)

---

## Requirements

- **Docker Desktop ≥ 4.x** (includes docker compose) _or_ a local PHP/Node toolchain:
  - PHP 8.2+, Composer
  - Node 18+ (or 20+), pnpm/npm/yarn
  - SQLite/MySQL (optional for local DB), or Laravel's default sqlite

---

## Project layout

```
.
├─ backend/                 # Laravel API
│  ├─ app/ …
│  ├─ routes/api.php …
│  └─ .env.example
├─ frontend/                # Vue 3 + Vite + Vuetify app
│  ├─ src/ …
│  └─ .env.example (created by us; see below)
└─ docker-compose.yml
```

---

## Run locally without Docker

If you prefer running everything on your host machine.

### Backend (Laravel)

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate

# Optionally configure DB in .env, then run:
php artisan migrate --seed

# Start the API (defaults to :8000)
php artisan serve --host=127.0.0.1 --port=8000
```

### Frontend (Vue 3 + Vite)

```bash
cd frontend
cp .env.example .env   # or create .env as below

# When running backend locally on 127.0.0.1:8000
# point Vite to the API origin:
# .env
# VITE_API_BASE=http://127.0.0.1:8000/api

npm install   # or pnpm install / yarn
npm run dev

# App will be at http://localhost:5173 (or shown by Vite)
```

> When running locally (no Docker), **use a full URL** for `VITE_API_BASE` that points to your `php artisan serve` port, e.g. `http://127.0.0.1:8000/api`.

---

## Environment variable guide

| Scenario | `frontend/.env` (`VITE_API_BASE`) | Notes |
|---|---|---|
| **Docker compose (recommended)** | `/api` | Frontend Nginx reverse-proxies `/api` to `backend-nginx:80` inside the compose network. No CORS issues. |
| **Local dev (backend on `127.0.0.1:8000`)** | `http://127.0.0.1:8000/api` | Different origins, so CORS may apply. Configure Laravel CORS/Sanctum accordingly. |
| **Production build behind reverse proxy** | Usually `/api` | Prefer same-origin with a path prefix; otherwise set the full origin. |

> If you change ports in `docker-compose.yml`, you **do not** need to change `VITE_API_BASE` in Docker mode as long as the proxy keeps mapping `/api` → API container.

---

## Useful scripts (Start Project)

Frontend:
```bash
npm run dev      # Vite dev server
npm run build    # Production build
npm run preview  # Preview built assets
```

Backend:
```bash
php artisan serve
php artisan migrate
php artisan tinker
```

---

## Run with Docker

This starts **three containers**: PHP-FPM (Laravel), Nginx for API, and Vite/Node (frontend).

### Environment variables (Docker)

- **backend/.env** – copy from `.env.example` and configure DB, app key, etc. At minimum:
  ```env
  APP_NAME=Notes
  APP_ENV=local
  APP_KEY=base64:GENERATED_BY_ARTISAN
  APP_DEBUG=true
  APP_URL=http://backend-nginx

  # If using sqlite locally you can keep DB_ settings as default or adjust for MySQL
  ```

  Then generate the key inside the container the first time (see *Start the stack*).

- **frontend/.env** – when using our docker compose + Nginx proxy, the frontend should call the API through the same origin via a proxy path. **Set:**
  ```env
  VITE_API_BASE=/api
  ```
  > Do **not** put a full `http://127.0.0.1:8000` URL in Docker mode; the frontend Nginx already proxies `/api` to the API container.

### Start the stack

From the project root:

```bash
# build & start
docker compose up -d --build

# install backend deps & generate key (first time only)
docker compose exec backend-php composer install
docker compose exec backend-php php artisan key:generate
# run migrations / seed if needed
docker compose exec backend-php php artisan migrate --seed
```

### What you get

- **Frontend (Vite/Nginx)**: http://localhost:8080  
  - The app calls the API at **`/api`** (proxied to the backend by Nginx inside the network).
- **Backend (Nginx)**: http://localhost:8001  
  - Raw API (useful for debugging).

Stop everything:
```bash
docker compose down
```

---

## Troubleshooting

- **CORS / cookies (Sanctum):** If you decide to call the API across origins in local dev, ensure these are set in backend `.env`:
  ```env
  SESSION_DOMAIN=localhost
  SANCTUM_STATEFUL_DOMAINS=localhost:5173
  APP_URL=http://127.0.0.1:8000
  ```
  and configure `cors.php` accordingly.
- **Icons not rendering (Vuetify):** Ensure Vuetify is configured with `@vuetify/iconesets/mdi-svg` aliases and the app imports them in `main.ts`.
- **Ports already in use:** Edit `docker-compose.yml` port mappings; then `docker compose up -d` again.
- **Database:** For a quick start, SQLite works great in dev. For MySQL/Postgres, update the backend `.env` and run migrations.

---

## License

MIT (for assessment/demo purposes)
