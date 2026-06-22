# 💳 Dynamic QRIS — Web

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![Svelte](https://img.shields.io/badge/Svelte-5-FF3E00?logo=svelte&logoColor=white)
![Inertia.js](https://img.shields.io/badge/Inertia.js-2-9553E9?logo=inertia&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-7-646CFF?logo=vite&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?logo=tailwindcss&logoColor=white)

> Backend API + dashboard untuk generate QRIS dinamis dari QRIS statis via [QRIS-ify API](https://qrisify.adihub.my.id).

```
┌─────────────────┐         ┌──────────────────────┐         ┌─────────────────┐
│  Android App    │◄───────►│  Laravel Backend      │◄───────►│  QRIS-ify API   │
│  (Compose)      │  REST   │  (API + Dashboard)    │  REST   │                 │
│                 │         │                       │◄────────│  (Webhook)      │
└─────────────────┘         │  Svelte Dashboard     │         └─────────────────┘
                            └──────────────────────┘
```

## ✨ Fitur

- **API Proxy** — forward request ke QRIS-ify, Android tidak perlu handle API key langsung
- **Webhook Receiver** — terima notifikasi pembayaran, verifikasi HMAC-SHA256 signature
- **Dashboard** — statistik transaksi, list + detail, filter by status
- **Ubah Password** — single-user password management
- **Auto-Expire** — artisan command untuk expire transaksi yang sudah lewat waktu
- **Race Condition Safe** — pessimistic locking pada status update

## 🛠 Tech Stack

| Layer | Tech |
|-------|------|
| Framework | Laravel 12 |
| PHP | 8.2+ |
| Database | MySQL / MariaDB |
| Frontend | Svelte 5 via Inertia.js |
| Auth API | Laravel Sanctum (Bearer token) |
| Build | Vite |

## 🚀 Quick Start

```bash
# Clone
git clone https://github.com/bimoalfarrabi/dynamic-qris-web.git
cd dynamic-qris-web

# Install
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dynamic_qris
DB_USERNAME=root
DB_PASSWORD=

QRISIFY_BASE_URL=https://qrisify.adihub.my.id
QRISIFY_API_KEY=your-api-key-here
QRISIFY_WEBHOOK_SECRET=your-webhook-secret-here
```

```bash
# Migrate + seed
php artisan migrate --seed

# Build assets
npm run build

# Run
php artisan serve
```

Buka `http://localhost:8000` — login dengan:

| | |
|---|---|
| Email | `test@example.com` |
| Password | `password` |

## 📱 Android App

Companion Android app tersedia di repo terpisah:

👉 **[dynamic-qris-android](https://github.com/bimoalfarrabi/dynamic-qris-android)**

Untuk menghubungkan Android ke backend ini, generate token Sanctum:

```bash
php artisan tinker
>>> User::first()->createToken('android')->plainTextToken
```

Lalu set token di `android/local.properties`:

```properties
API_BASE_URL=http://10.0.2.2:8000/api/
API_TOKEN=token-dari-tinker
```

## 📡 API Endpoints

Header required: `Authorization: Bearer {token}`

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/api/transactions` | List (filter: status, search, from, to, page) |
| `GET` | `/api/transactions/{id}` | Detail transaksi |
| `POST` | `/api/transactions` | Buat transaksi baru |
| `POST` | `/api/transactions/{id}/cancel` | Batalkan transaksi pending |
| `POST` | `/api/webhook/qrisify` | Webhook dari QRIS-ify |

### Create Transaction Request

```json
{
  "amount": 50000,
  "external_id": "INV-001",
  "expiry_minutes": 30
}
```

### Webhook

QRIS-ify mengirim notifikasi pembayaran ke `POST /api/webhook/qrisify`.

- Signature di header `X-Qrisify-Signature`
- Verifikasi: HMAC-SHA256 dari request body dengan `QRISIFY_WEBHOOK_SECRET`
- Transaksi yang sudah CANCELLED akan di-reject

## 🧪 Testing

```bash
php artisan test
```

30 tests, 148 assertions — mencakup transaction API, webhook verification, race conditions, dan auto-expire.

## 🔧 Artisan Commands

```bash
# Expire transaksi yang sudah lewat expiry time
php artisan transactions:expire

# Generate API token
php artisan tinker
>>> User::first()->createToken('name')->plainTextToken
```

## 📁 Struktur

```
app/
├── Http/Controllers/
│   ├── Api/TransactionController.php   # REST API
│   ├── AuthController.php              # Login + change password
│   ├── DashboardController.php         # Inertia pages
│   └── WebhookController.php           # QRIS-ify webhook
├── Models/Transaction.php              # UUID, enum casting
├── Enums/TransactionStatus.php         # PENDING, SUCCESS, EXPIRED, CANCELLED
└── Services/QrisifyService.php         # API client
resources/js/
├── pages/
│   ├── Dashboard.svelte
│   ├── Transactions/Index.svelte
│   ├── Transactions/Show.svelte
│   ├── ChangePassword.svelte
│   └── Login.svelte
└── components/
    ├── Layout.svelte
    └── StatusBadge.svelte
```

## 📄 Lisensi

MIT
