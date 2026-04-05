# Panchayat Chatbot

A community management web app for residential societies — built with Laravel 11. Residents can file complaints, chat with a bot, vote on polls, attend events, and discuss in a forum. Admins manage everything from a central dashboard.

---

## Features

| Module | What it does |
|--------|-------------|
| Chatbot | Rule-based bot that answers common questions (9 intents) |
| Complaints | File text or voice complaints, track status, upvote, attach files |
| Announcements | Admin publishes notices with publish/expire dates |
| Polls | Residents vote, results shown as charts |
| Events | Create events, residents RSVP |
| Forum | Discussion threads with nested replies |
| Security | Incident reports, patrol assignments, emergency alerts |
| Analytics | Charts (doughnut, bar, trend), CSV export |
| Notifications | In-app notification system |

---

## User Roles

| Role | Access |
|------|--------|
| **Admin** | Full dashboard — complaints, announcements, events, polls, analytics, rule book |
| **Security Head** | Security incidents, patrols, emergency alerts |
| **Resident** | File complaints, chat, vote, RSVP events, forum |

---

## Tech Stack

- **Backend:** PHP 8.2, Laravel 11
- **Frontend:** Blade, Tailwind CSS, Alpine.js
- **Charts:** Chart.js
- **Database:** SQLite
- **Auth:** Laravel Breeze

---

## Getting Started

### Requirements

- PHP 8.2+
- Composer
- Node.js & npm

### Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Install JS dependencies
npm install

# 3. Copy environment file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Create the SQLite database file
touch database/database.sqlite

# 6. Run migrations and seed demo data
php artisan migrate --seed
```

### Running the App

```bash
# Start everything (server + queue + vite)
composer run dev
```

Then open http://localhost:8000 in your browser.

---

## Demo Accounts

All accounts use the password: `password`

| Role | Email |
|------|-------|
| Admin | admin@panchayat.local |
| Security Head | security@panchayat.local |
| Resident | ramesh@example.com |
| Resident | priya@example.com |
| Resident | suresh@example.com |

To reset demo data: `php artisan db:seed --force`

---

## Project Structure

```
app/
  Http/Controllers/   — Route controllers
  Models/             — Eloquent models
  Services/           — ChatBotService and other logic
database/
  migrations/         — 22 database tables
  seeders/            — Demo data
resources/views/      — Blade templates
routes/web.php        — All routes
```

---

## License

MIT
