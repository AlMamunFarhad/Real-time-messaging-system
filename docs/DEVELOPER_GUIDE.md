# Real-time Messaging System — Developer Guide (Bangla)

এই ডকুমেন্টটা **Messaging System** কে যেকোনো Laravel প্রজেক্টে ইমপ্লিমেন্ট করার জন্য।  
**Login/Auth system এখানে দেয়া নেই**—আপনার প্রজেক্টের existing authentication (guards / middleware) ব্যবহার করেই messaging চলবে।

> Target: Laravel 12 + Reverb + Vite (Echo + Pusher JS)

---

## 1) Feature Overview

আপনি যেগুলো পাবেন:

- 1-to-1 (Private) conversation + message history
- Real-time message broadcasting (WebSockets) via **Laravel Reverb**
- Unread counter (badge)
- Conversation participants polymorphic (Admin/User বা আপনার নিজের models)
- Online status heartbeat/check routes
- Web UI (Blade + Alpine) — Admin panel chat + User popup chat
- API routes (Sanctum) ভিত্তিক structure (optional)

---

## 2) Requirements

Minimum:

- PHP `^8.2`
- Laravel `^12`
- Node.js (Vite build)
- Database (MySQL recommended)

Packages (এই রিপোতে already আছে):

- `laravel/reverb`
- `nwidart/laravel-modules`
- Frontend: `laravel-echo`, `pusher-js`, `alpinejs`, `tailwindcss`

---

## 3) How This Messaging Works (Quick Mental Model)

### Database

Messaging module 3টা টেবিল তৈরি করে:

- `conversations` (type: `private`)
- `conversation_participants` (polymorphic: `participant_type` + `participant_id`)
- `messages` (polymorphic sender + body/file metadata)

### Broadcasting Channels (Private)

Event: `Modules/Messaging/app/Events/MessageSent.php`

- Conversation room: `private-conversation.{conversationId}`
- User notification: `private-user.{typeShort}.{id}`  
  উদাহরণ: `private-user.admin.1`, `private-user.user.5`

Channel auth rules: `routes/channels.php`

---

## 4) “Copy + Integrate” Checklist (What You Need To Bring Into Your Project)

আপনি আপনার Laravel প্রজেক্টে নিচের জিনিসগুলো নিয়ে যাবেন:

1) `Modules/Messaging/` (পুরো ফোল্ডার)
2) `routes/channels.php` এর Messaging-related channel rules
3) Frontend Echo bootstrap: `resources/js/echo.js` + `resources/js/bootstrap.js`
4) UI component: `resources/views/components/message-icon.blade.php`
5) (Optional) Admin page routes/UI: `routes/web.php` এর admin messages অংশ (আপনার admin panel অনুযায়ী adapt করবেন)

---

## 5) Install Steps (In Your Target Laravel Project)

### Step A — Packages

আপনার প্রজেক্টে নিশ্চিত করুন:

```bash
composer require laravel/reverb nwidart/laravel-modules
npm i laravel-echo pusher-js
```

> এই repo তে `composer.json`/`package.json` এ already আছে—আপনার প্রজেক্টে missing থাকলে add করুন।

### Step B — Add The Module Folder

আপনার প্রজেক্ট root এ `Modules/Messaging/` কপি করুন।

তারপর autoload refresh:

```bash
composer dump-autoload
```

> এই প্রজেক্টে module provider `Modules/Messaging/app/Providers/MessagingServiceProvider.php` ব্যবহার হচ্ছে, `nwidart/laravel-modules` নিজে থেকেই module load করবে (module.json presence ভিত্তিতে)।

### Step C — Run Migrations

Messaging module migrations রান করুন:

```bash
php artisan module:migrate Messaging
```

যদি আপনার setup এ module migrate command না কাজ করে, তাহলে fallback:

- `Modules/Messaging/database/migrations/*` ফাইলগুলো main `database/migrations` এ কপি করে `php artisan migrate`

### Step D — Broadcasting + Reverb Configure

`.env` এ (example):

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=local
REVERB_APP_KEY=local
REVERB_APP_SECRET=local

REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
```

Vite env (same `.env` এ):

```env
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Reverb server চালু করুন:

```bash
php artisan reverb:start
```

> Production এ সাধারণত supervisor/systemd দিয়ে চালানো হয়।

### Step E — Frontend Echo Bootstrap

এই repo’র মত করে আপনার প্রজেক্টে নিশ্চিত করুন:

- `resources/js/echo.js` আছে এবং Echo Reverb config করে
- `resources/js/bootstrap.js` এ `import './echo';` আছে

Reference file:

- `resources/js/echo.js`
- `resources/js/bootstrap.js`

তারপর assets build:

```bash
npm run build
```

---

## 6) Auth / Login (আপনার প্রজেক্টে যেটা আছে সেটাই থাকবে)

Messaging module বর্তমানে 2টা guard ধরে নেয়:

- `admin`
- `web`

এটা define করা আছে:

- `Modules/Messaging/app/Helpers/AuthParticipant.php`
- `Modules/Messaging/routes/web.php` (middleware: `auth:admin,web`)
- `routes/channels.php` (channel authorization)

### Option A — আপনার প্রজেক্টেও `admin` + `web` guards আছে (সবচেয়ে সহজ)

কিছুই change না করলেও চলবে, শুধু ensure করুন:

- `auth.php` এ guard দুটো define আছে
- `App\Models\Admin` এবং `App\Models\User` model আছে (অথবা mapping adjust করবেন)

### Option B — আপনার প্রজেক্টে single guard (যেমন শুধু `web`)

আপনি ৩ জায়গায় update করবেন:

1) `Modules/Messaging/app/Helpers/AuthParticipant.php`
   - `protected static $guards = ['web'];`
   - `type()` mapping আপনার user model এ দিন

2) `Modules/Messaging/routes/web.php`
   - `->middleware(['web', 'auth'])` বা আপনার middleware name

3) `routes/channels.php`
   - `user.admin.{id}` / `user.user.{id}` অংশ simplify করে আপনার guard অনুযায়ী করুন
   - `conversation.{conversationId}` check এ guard logic adjust করুন

> Rule of thumb: **যে auth guard আপনার site এ logged-in user কে identify করে, সেই guard দিয়েই channel authorize করবেন।**

---

## 7) Routes You Get (Messaging Module)

Module web routes: `Modules/Messaging/routes/web.php`

Important endpoints:

- `POST /send-message` → `messages.send`
- `POST /mark-read` → `messages.markRead`
- `GET /messages/conversations` → `messages.conversations` (badge)
- `GET /messages/{conversationId}` → `messages.load` (web message fetch)
- `GET /chat/{userId}/{type}` → `chat.index`
- `GET /chat/{conversationId}` → `chat.show`
- Online:
  - `POST /online-heartbeat` → `online.heartbeat`
  - `GET /online-status/{userId}/{type}` → `online.check`

API routes (optional): `Modules/Messaging/routes/api.php`

- Sanctum middleware based

---

## 8) UI Integration

### A) Message Icon (Navbar/Topbar)

Component file:

- `resources/views/components/message-icon.blade.php`

Use it anywhere:

```blade
<x-message-icon />
```

Admin dashboard এ এটা `admin.messages` route এ নিয়ে যায়।  
User side এ এটা popup chat খুলে।

> আপনার প্রজেক্টে যদি admin route নাম আলাদা হয়, `message-icon.blade.php` এ route নামটা update করুন।

### B) Admin Chat Page

View:

- `Modules/Messaging/resources/views/chat/admin.blade.php`

এটার জন্য এই repo তে route আছে:

- `routes/web.php` → `GET /admin/messages` → `ChatController@adminDashboard` (`admin.messages`)

আপনার প্রজেক্টে:

- আপনার admin panel layout/component (`x-admin-layout`) অনুযায়ী view adapt করবেন
- guard/middleware আপনার admin auth অনুযায়ী দেবেন

---

## 9) Broadcasting Channels (Must Have)

File:

- `routes/channels.php`

Messaging require করে:

- `Broadcast::channel('user.admin.{id}', ...)`
- `Broadcast::channel('user.user.{id}', ...)`
- `Broadcast::channel('conversation.{conversationId}', ...)`

আপনার guard/model অনুযায়ী এই rules গুলো adjust করবেন।

> Debugging tip: `conversation.{conversationId}` channel authorization false হলে real-time event আসবে না।

---

## 10) Local Dev Run Commands (Suggested)

Backend:

```bash
php artisan serve
php artisan reverb:start
```

Frontend:

```bash
npm run dev
```

> এই repo তে `composer run dev` command আছে (server + queue + logs + vite একসাথে চালায়)।

---

## 11) Troubleshooting (Common Issues)

### 1) Real-time event আসে না

Checklist:

- `.env` এ `BROADCAST_CONNECTION=reverb`
- `php artisan reverb:start` চলছে
- Frontend এ Echo config correct (`VITE_REVERB_*`)
- `routes/channels.php` authorization true হচ্ছে কিনা

### 2) 401/403 on private channels

- Guard mismatch (admin/web vs আপনার guard)
- `AuthParticipant` mapping mismatch
- Session/CSRF issue (web routes এ)

### 3) Unread badge update হচ্ছে না

- `messages.conversations` route authenticated কিনা দেখুন
- component এ `conversationsUrl` route name আপনার প্রজেক্টে same কিনা

---

## 12) What You Should Customize First (Recommended)

সবচেয়ে আগে এগুলো কাস্টমাইজ করবেন:

1) `Modules/Messaging/app/Helpers/AuthParticipant.php` (guards + model mapping)
2) `routes/channels.php` (channel authorization)
3) `Modules/Messaging/routes/web.php` middleware
4) UI routes names (admin/user) আপনার app অনুযায়ী

---

## 13) Integration “Done” Definition (Quick Verification)

আপনি ধরে নিতে পারেন integration complete, যদি:

- Module migrations migrated ✅
- Login করে message send করলে DB তে `messages` insert হচ্ছে ✅
- `php artisan reverb:start` চলছে ✅
- Browser console এ Echo connect OK ✅
- অন্য user/admin side এ real-time message instantly দেখা যাচ্ছে ✅

---

## 14) Repo Reference Files (Quick Links)

- Module: `Modules/Messaging/`
- Auth helper: `Modules/Messaging/app/Helpers/AuthParticipant.php`
- Web routes: `Modules/Messaging/routes/web.php`
- API routes: `Modules/Messaging/routes/api.php`
- Event: `Modules/Messaging/app/Events/MessageSent.php`
- Channel rules: `routes/channels.php`
- Echo config: `resources/js/echo.js`
- Bootstrap: `resources/js/bootstrap.js`
- UI component: `resources/views/components/message-icon.blade.php`

