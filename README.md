# Nexora — Social Networking Platform

Nexora is a full-stack social networking application: a **Laravel 13 / PHP 8.3** modular-monolith REST API backed by **PostgreSQL (Neon)**, with a **NativePHP** Android mobile client that consumes the API.

> **Build status (Day 1):** Foundation, Authentication (JWT), Users & Profiles, and the full Friendship system are implemented, tested end-to-end, and live. The NativePHP mobile app (auth + profile + friends) is built. Posts/Feed, Realtime Chat + Notifications, and WebRTC Calling are scaffolded and scheduled for the next iteration. Reels were descoped.

---

## Tech Stack

| Concern            | Choice                                                        |
|--------------------|---------------------------------------------------------------|
| Framework          | Laravel 13, PHP 8.3                                            |
| Database           | PostgreSQL 18 (Neon serverless)                               |
| Auth               | JWT (`php-open-source-saver/jwt-auth`)                        |
| Realtime           | Laravel Reverb (WebSockets) — *wired, used next iteration*    |
| Background work    | Laravel Queues (database driver; Redis in Docker)            |
| Media / images     | Intervention Image v4                                         |
| Video              | FFmpeg (`pbmedia/laravel-ffmpeg`) — *config ready*           |
| Mobile             | NativePHP Mobile v3 (Android)                                 |
| Architecture       | Modular monolith · Clean Architecture · Service + Repository  |

---

## Architecture

A **modular monolith**: one deployable application, internally split into self-contained domain modules under `app/Modules/`.

```
app/Modules/
├── Shared/        # Base controller, ApiResponse envelope, BaseRepository/Service, MediaService, middleware
├── Auth/          # Register, login, JWT, email verification, password reset
├── User/          # User + Profile models, profile management, search
├── Friendship/    # Requests, accept/decline/cancel, unfriend, block/unblock, suggestions
├── Post/          # (next iteration)
├── Reel/          # (descoped)
├── Chat/          # (next iteration)
├── Call/          # (next iteration)
└── Notification/  # (next iteration)
```

Each module follows the same layering:

```
Models/  Repositories/{Contracts,Eloquent}/  Services/  Http/{Controllers,Requests,Resources}/  Events/
```

**Request flow:** `Route → Controller (thin) → Service (business rules, transactions) → Repository (interface) → Eloquent`.
Controllers never touch Eloquent directly; services depend on repository **interfaces** bound to Eloquent implementations in `App\Providers\DomainServiceProvider`.

Every API response uses one JSON envelope (`App\Modules\Shared\Http\ApiResponse`):

```json
{ "success": true, "message": "OK", "data": { }, "meta": { } }
```

---

## Implemented API (prefix `/api/v1`)

**Auth** — `POST /auth/register`, `POST /auth/login`, `POST /auth/logout`, `POST /auth/refresh`, `GET /auth/me`,
`GET /auth/email/verify/{id}/{hash}` (signed), `POST /auth/email/resend`, `POST /auth/forgot-password`,
`POST /auth/reset-password`, `POST /auth/change-password`.

**Users / Profile** — `GET /users/search?q=`, `GET /users/{username}`, `PATCH /profile`,
`POST /profile/avatar`, `POST /profile/cover`, `DELETE /profile` (deactivate).

**Friendship** — `GET /friends`, `GET /friends/suggestions`, `GET /friends/requests/{incoming|outgoing}`,
`GET /friends/blocked`, `POST /friends/requests/{user}` (+ `/accept`, `/decline`), `DELETE /friends/requests/{user}` (cancel),
`DELETE /friends/{user}` (unfriend), `POST|DELETE /friends/{user}/block`.

Protected routes require `Authorization: Bearer <token>`.

---

## Backend Setup

Requirements: PHP 8.3 (ext: `pdo_pgsql`, `gd`, `mbstring`, `zip`, `bcmath`), Composer, Node 20+.

```bash
composer install
cp .env.example .env        # then set the DB / mail / JWT values (see .env)
php artisan key:generate
php artisan jwt:secret
php artisan storage:link
php artisan migrate
php artisan serve            # http://127.0.0.1:8000
php artisan queue:work       # processes queued mail/notifications
```

> **Neon note:** use the **direct** endpoint (`...neon.tech`, no `-pooler`) in `DB_HOST`.
> The PgBouncer pooler endpoint aborts the multi-statement transactions that migrations
> and the service layer rely on. The pooler host is kept commented in `.env` for reference.

Realtime (next iteration): `php artisan reverb:start`.

---

## Mobile App (NativePHP — Android)

The mobile app is a thin client: an on-device Laravel renders the Blade/Alpine UI
(`resources/views/mobile/app.blade.php`, served at `/app`) which calls the **remote**
REST API over HTTP with the JWT. NativePHP strips DB secrets at bundle time, so the
device never connects to the database directly.

### Configure
In `.env`:
```
NATIVEPHP_APP_ID=com.nexora.app
NATIVEPHP_START_URL=/app
MOBILE_API_URL=https://your-api-host.com/api/v1   # the deployed backend
```
- **Android emulator → host machine:** use `http://10.0.2.2:8000/api/v1` (not `localhost`).
- **Production:** point `MOBILE_API_URL` at your public API URL.

### Build / Run (requires the Android toolchain)
Install **Android Studio + SDK + JDK 17** first (`php artisan native:debug` verifies this), then:
```bash
php artisan native:jump      # run on a connected device / emulator (live)
php artisan native:open      # open the generated Android Studio project
php artisan native:package   # produce a signed APK/AAB for distribution
```
The Android project + embedded PHP runtime are generated under `nativephp/`.

### Mobile features (Day 1)
Onboarding → Login / Register · Home (search people, friend suggestions) ·
Friends list · Friend requests (incoming/outgoing, confirm/decline/cancel) ·
Profile view & edit · Avatar upload · Other-user profiles with add/cancel/unfriend/block ·
Settings (change password, logout). Token persisted on device with automatic refresh on 401.

---

## Testing the API quickly

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/register -H "Accept: application/json" \
  -d 'name=Jane Doe&username=jane&email=jane@example.com&password=Password123&password_confirmation=Password123'
```
