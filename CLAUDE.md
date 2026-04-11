# Panchayat Chatbot — Claude Code Guidelines

## Confidence Before Changes

**Do not make any code changes until you have 95% confidence in what needs to be built.**

Before implementing anything non-trivial, ask follow-up questions to reach that confidence level. Clarify:
- What exactly needs to change and why
- Any edge cases or constraints
- Whether the approach is correct

Only proceed once the requirement is fully understood.

---

## Project Overview

Laravel 11 community management web app with 3 roles (admin, resident, security_head), featuring:
- Complaint management (filing, tracking, categories, voice recording, attachments, upvotes)
- Community events with RSVP
- Polls/voting
- Forum threads and replies
- Security incident & patrol tracking
- Announcements and rule book
- AI chatbot (ChatController with session-based history)
- Notification system (polling-based, no WebSockets)

---

## Tech Stack

### Backend
- **PHP** ^8.2
- **Laravel** ^11.31
- **Database**: PostgreSQL (production/Railway), SQLite (local dev)
- **Auth**: Laravel Breeze (session-based)
- **Roles**: Custom `roles` table + `role_id` FK on `users` (not Spatie)

### Frontend
- **Blade** templates with Tailwind CSS ^3
- **Alpine.js** ^3.4.2 — all interactivity (modals, dropdowns, dark mode, notifications)
- **Vite** ^6 + `laravel-vite-plugin` — asset bundling
- **Chart.js** ^4.5 — analytics/dashboard charts
- **@tailwindcss/forms** — form element base styles
- **Axios** — HTTP requests (chat, upvotes)
- **No Pusher / No WebSockets** — notifications poll every 30s via Alpine.js

### Key Models
`User`, `Role`, `Complaint`, `ComplaintCategory`, `ComplaintMedia`, `ComplaintUpdate`, `ComplaintUpvote`,
`Event`, `EventRsvp`, `Poll`, `PollOption`, `PollVote`, `Announcement`, `ForumThread`, `ForumReply`,
`SecurityIncident`, `PatrolAssignment`, `EmergencyAlert`, `RuleBookSection`, `ChatSession`, `ChatMessage`,
`Notification`, `Feedback`

### Controller Namespaces
- `App\Http\Controllers\Admin\` — admin-only resources
- `App\Http\Controllers\Resident\` — resident-only resources
- `App\Http\Controllers\Security\` — security_head-only resources
- `App\Http\Controllers\Shared\` — multi-role shared features

---

## Build Commands

```bash
# Install dependencies
composer install
npm install

# Development (runs Vite dev server)
npm run dev

# Production build
npm run build

# Database
php artisan migrate
php artisan migrate:fresh --seed   # reset + seed demo data
php artisan db:seed

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Artisan helpers
php artisan route:list              # list all routes
php artisan make:model Foo -mcr     # model + migration + resource controller
php artisan make:controller Admin/FooController --resource
php artisan tinker
```

---

## Coding Conventions

### General
- Follow PSR-12 for PHP
- Use `snake_case` for DB columns and route parameters
- Use `camelCase` for PHP variables and methods
- Use `PascalCase` for class names
- Prefer explicit type hints on all method signatures (return types + parameters)

### Controllers
- Thin controllers — business logic belongs in models or service classes if complex
- Use `$request->validate([...])` directly in controller (auto-redirects back on failure)
- Always redirect to `index` after `store`/`update`/`destroy`, never to `show`
- Pass only needed data to views via `compact()`

### Blade / Views
- All CRUD forms are in **modals** using the `<x-modal>` Blade component
- Modal trigger: `window.dispatchEvent(new CustomEvent('open-modal', { detail: 'modal-name' }))`
- Modal close inside slot: `@click="show = false"`
- Create modal reopens on validation failure: `:show="$errors->any() && !old('_edit_id')"`
- Edit modal reopens for correct row: `:show="old('_edit_id') == $item->id && $errors->any()"`
- Edit forms include `<input type="hidden" name="_edit_id" value="{{ $item->id }}">`
- Delete actions always use confirmation **modals** — never native `confirm()` JS dialogs
- Flash messages use `session('success')` and `session('error')`

### Alpine.js
- Wrap interactive components with `x-data="{ ... }"`
- Use `$dispatch('open-modal', 'name')` only inside existing Alpine scope; otherwise use `window.dispatchEvent(...)`
- Dark mode toggle stored in `localStorage`, managed in `layouts/app.blade.php`

### Routes
- Grouped by middleware: `auth`, then `role:admin` / `role:resident` / `role:security_head`
- Route names follow: `admin.{resource}.{action}`, `resident.{resource}.{action}`, `security.{resource}.{action}`, `forum.{action}`

### Migrations
- Always add `->nullable()` for optional fields
- Use `foreignId('user_id')->constrained()->cascadeOnDelete()` pattern
- Soft deletes only where data retention matters (e.g., Complaints)

---

## Enums

Located in `app/Enums/`. Back all enums with `string` type:
- `EventStatus` — `upcoming`, `ongoing`, `completed`, `cancelled`
- Complaint and priority statuses use string-backed enums with `badgeClass()` and `label()` helpers

---

## File Structure Notes

```
app/
  Enums/           — PHP 8.1 backed string enums
  Http/
    Controllers/
      Admin/       — admin-scoped controllers
      Resident/    — resident-scoped controllers
      Security/    — security_head-scoped controllers
      Shared/      — multi-role controllers (Forum, Chat, Events, Polls...)
    Middleware/    — CheckRole middleware for role-based access
  Models/          — Eloquent models (all flat, no subdirectories)
  Policies/        — authorization policies

resources/
  views/
    admin/         — admin Blade views
    resident/      — resident Blade views
    security/      — security_head Blade views
    shared/        — shared views (forum, events, polls, chat...)
    layouts/       — app.blade.php (main layout with nav, Alpine components)
    components/    — Blade components (modal, delete-form, stat-card, etc.)
```
