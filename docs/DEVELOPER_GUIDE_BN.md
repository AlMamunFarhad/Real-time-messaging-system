# Real-time Messaging System Developer Guide

Ei guide ta emon vabe likha hoyeche jate ekjon Laravel developer sudhu ei document follow kore ei messaging system nijer project e integrate korte pare. Login/auth system ekhane impose kora hoy na; apnar existing auth, guard, middleware, layout, route naming use kore messaging bosate parben.

## 1. What You Are Getting

Ei system e ache:

- 1-to-1 private conversation
- Real-time messaging via Laravel Reverb
- Unread badge counter
- Read status tracking
- Polymorphic participants and sender support
- Admin side conversation panel
- User side popup message panel
- Online status heartbeat/check endpoints
- Optional API structure for external/mobile extension

## 2. Best Use Case

Eta especially useful jodi apnar project e:

- already login system thake
- admin ebong user er moddhe chat lagbe
- support, inquiry, internal message, CRM-style inbox lagbe
- Blade based dashboard use kora hoy

Jodi apnar project React/Vue/Inertia hoy, backend part same thakbe; UI part custom korte hobe.

## 3. High-level Architecture

System ta 4 ta layer e kaaj kore:

1. Database layer  
   `conversations`, `conversation_participants`, `messages`

2. Business logic layer  
   Module controller, model, helper, event

3. Realtime layer  
   Laravel Reverb + Echo + private broadcast channels

4. UI layer  
   Admin chat page + message icon + user popup panel

## 4. Core Files You Need To Understand

Shuru te ei file gulo dekhe nile pura system ta quickly bujha jay:

- `Modules/Messaging/app/Helpers/AuthParticipant.php`
- `Modules/Messaging/app/Http/Controllers/MessageController.php`
- `Modules/Messaging/app/Http/Controllers/MessagingController.php`
- `Modules/Messaging/app/Http/Controllers/ChatController.php`
- `Modules/Messaging/app/Events/MessageSent.php`
- `Modules/Messaging/routes/web.php`
- `routes/channels.php`
- `resources/views/components/message-icon.blade.php`
- `Modules/Messaging/resources/views/chat/admin.blade.php`
- `resources/js/echo.js`

## 5. Integration Strategy

Ei system ke onno project e niye jawar easiest strategy hocche:

1. Messaging module copy korun
2. Required package install korun
3. Migration run korun
4. Broadcasting/Reverb configure korun
5. Auth mapping adjust korun
6. Channel authorization adjust korun
7. UI component boshan
8. End-to-end test korun

## 6. Dependencies

Project e minimum eta thaka uchit:

- PHP `8.2+`
- Laravel `12.x`
- Node.js
- Vite
- Database

Required package:

```bash
composer require laravel/reverb nwidart/laravel-modules
npm install laravel-echo pusher-js
```

Optional but useful:

- `alpinejs`
- `tailwindcss`

## 7. Files To Copy Into Another Project

Minimum transferable parts:

- `Modules/Messaging/`
- `resources/views/components/message-icon.blade.php`
- `resources/js/echo.js`
- messaging related channel rules from `routes/channels.php`

Project-specific pieces that usually need adaptation:

- admin routes in `routes/web.php`
- admin layout usage like `x-admin-layout`
- guard names
- model namespace
- route names

## 8. Database Design

### `conversations`

- stores conversation shell
- currently `type` defaults to `private`

### `conversation_participants`

- links users/admins with a conversation
- polymorphic participant support
- has `last_read_at`
- has `joined_at`

### `messages`

- stores each message row
- sender is polymorphic
- supports text and file
- has `read_at`
- has soft delete style flag `is_deleted`

## 9. Auth Model

Current implementation assumes two auth guards:

- `admin`
- `web`

Current mapping lives in:

- `Modules/Messaging/app/Helpers/AuthParticipant.php`

Default type mapping:

- `admin` -> `App\\Models\\Admin`
- `web` -> `App\\Models\\User`

### If your project uses the same guards

Tahle integration onek easy. Mostly route/layout naming adjust korlei cholbe.

### If your project uses only one guard

Tahole first ei file ta change korun:

- `Modules/Messaging/app/Helpers/AuthParticipant.php`

Typical change:

```php
protected static $guards = ['web'];
```

And `type()` method e nijer model mapping din.

### If your project uses custom guards

Dhore nei:

- `customer`
- `staff`

Tahole apnake update korte hobe:

- `AuthParticipant.php`
- `Modules/Messaging/routes/web.php`
- `routes/channels.php`
- jekhane route middleware e `auth:admin,web` use kora ache

## 10. Route Overview

### Messaging module web routes

Source:

- `Modules/Messaging/routes/web.php`

Important route list:

- `POST /send-message`
- `POST /mark-read`
- `GET /messages/conversations`
- `GET /messages/{conversationId}`
- `GET /chat/{userId}/{type}`
- `GET /chat/{conversationId}`
- `POST /online-heartbeat`
- `GET /online-status/{userId}/{type}`

### Extra app routes used in this repo

Source:

- `routes/web.php`

Important route list:

- `GET /admin/messages`
- `GET /admin/messages/conversation`
- `GET /user/messages/conversation`
- `GET /admin/users/list`

Ei route gulo mainly UI convenience er jonno, module core backend er jonno na.

## 11. Broadcasting Design

### Event

- `Modules/Messaging/app/Events/MessageSent.php`

Event ta multiple private channel e broadcast kore:

- `conversation.{conversationId}`
- `user.admin.{id}`
- `user.user.{id}`

### Channel authorization

Source:

- `routes/channels.php`

Realtime system stable korte hole ei file ta apnar auth system er sathe perfectly align korte hobe.

Jodi ekhane guard mismatch thake, tahole:

- unread badge sync fail korte pare
- chat live update fail korte pare
- private channel 403 dite pare

## 12. Frontend Realtime Setup

Frontend Echo setup ache:

- `resources/js/echo.js`
- `resources/js/bootstrap.js`
- `resources/js/app.js`

Current setup Reverb use kore. Ensure korun je:

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

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Run:

```bash
php artisan reverb:start
npm run dev
```

Production e build:

```bash
npm run build
```

## 13. Step-by-step Integration Guide

### Step 1. Module add korun

Apnar target project root e `Modules/Messaging/` copy korun.

Tarpor:

```bash
composer dump-autoload
```

### Step 2. Module active ache kina check korun

Ensure:

- `module.json` present
- `modules_statuses.json` e module enabled
- `nwidart/laravel-modules` installed

### Step 3. Migration run korun

```bash
php artisan module:migrate Messaging
```

Fallback:

- `Modules/Messaging/database/migrations` er file gulo main migration folder e niye `php artisan migrate`

### Step 4. Broadcasting configure korun

Check files:

- `config/broadcasting.php`
- `config/reverb.php`
- `.env`

### Step 5. Channel rules add korun

Apnar `routes/channels.php` e messaging related private channel authorization boshan.

Minimum concept:

- current logged in user nijer personal channel access pabe
- user sudhu nijer conversation channel access pabe

### Step 6. AuthParticipant adapt korun

File:

- `Modules/Messaging/app/Helpers/AuthParticipant.php`

Eta hocche puro messaging system er auth bridge.

Jodi ei file thik moto adapt na koren, tahole:

- message load fail korte pare
- send fail korte pare
- badge count wrong aste pare
- admin/user detection wrong hobe

### Step 7. UI component boshan

Navbar/topbar e:

```blade
<x-message-icon />
```

Eta user/admin duijoner jonnoi use kora jay, but route/layout dependency thik thakte hobe.

### Step 8. Admin chat page wire korun

Current repo te admin page:

- `Modules/Messaging/resources/views/chat/admin.blade.php`

Eta use korte hole ensure korun:

- admin route ache
- admin layout ache
- user list endpoint ache
- admin auth middleware thik ache

### Step 9. User popup route wire korun

Current repo te user popup icon route use kore:

- `user.messages.conversation`

Ensure ei route apnar app e ache ba equivalent kichu ache.

### Step 10. Build and test korun

```bash
php artisan optimize:clear
php artisan serve
php artisan reverb:start
npm run dev
```

## 14. Quick Verification Checklist

Integration sesh hole ei 10 ta point check korun:

1. Login kora user icon dekhte pacche
2. Admin side icon dekhte pacche
3. Message send hole `messages` table e row insert hocche
4. New conversation hole `conversations` and `conversation_participants` fill hocche
5. Admin unread badge count update hocche
6. User unread badge count update hocche
7. Admin panel e user select kore chat open hocche
8. User panel e admin chat open hocche
9. Realtime e page refresh chara message ashche
10. Read mark hole counter komche

## 15. Common Customization Points

Ei project onno jaygay niye gele sobcheye beshi ekhane change lagbe:

- guard names
- model namespace
- route names
- admin layout/component name
- admin users list source
- message icon placement
- popup design
- file storage path

## 16. Troubleshooting

### Problem: unread badge kaj korche na

Check:

- `messages.conversations` route authenticated kina
- component poll request 200 dicche kina
- auth guard properly detect hocche kina
- `AuthParticipant` thik model return korche kina

### Problem: realtime message ashche na

Check:

- Reverb running kina
- browser console e websocket error ache kina
- channel authorization 403 dicche kina
- event dispatch hocche kina
- `BROADCAST_CONNECTION=reverb` set kina

### Problem: admin side kaj kore, user side kore na

Check:

- `user.messages.conversation` route ache kina
- user popup component branch thik route use korche kina
- `web` guard detect hocche kina

### Problem: user side kaj kore, admin side kore na

Check:

- `admin` guard detect hocche kina
- `admin.messages` route ache kina
- admin layout e component render hocche kina
- `routes/channels.php` admin authorization thik kina

### Problem: 401 or 403

Usually root cause:

- guard mismatch
- middleware mismatch
- channel auth mismatch
- session/CSRF issue

## 17. Recommended Refactor If You Want To Reuse This Everywhere

Jodi apni eta reusable package-level quality korte chan, tahole next step hote pare:

1. `AuthParticipant` ke config-driven kora
2. route names config-driven kora
3. admin/user model mapping config-driven kora
4. message icon ke smaller partial/component e split kora
5. JS logic inline script theke external file e niye jawa
6. admin and user UI alada component e split kora

Ei gula korle future project e integration aro fast hobe.

## 18. Recommended Minimum Files To Edit In A New Project

Jodi khub minimum edit diye integration korte chan, tahole prothome ei file gulo touch korun:

- `Modules/Messaging/app/Helpers/AuthParticipant.php`
- `Modules/Messaging/routes/web.php`
- `routes/channels.php`
- `resources/views/components/message-icon.blade.php`
- your app `routes/web.php`

## 19. Command Reference

```bash
composer dump-autoload
php artisan module:migrate Messaging
php artisan optimize:clear
php artisan serve
php artisan reverb:start
npm run dev
npm run build
```

## 20. Final Notes

Ei messaging system ta directly plug-and-play na, but eta strongly reusable. Main kaj hocche auth layer, route naming, and UI placement align kora. Ei 3 ta thik kore dile backend logic, unread counting, conversation structure, and realtime flow onno project eo reliably kaaj korbe.

Jodi apni chan, next step e ami ei doc er sathe ekta `INTEGRATION_CHECKLIST.md` ba `COPY_FILES_LIST.md` o add kore dite pari jeta developer handoff er jonno aro useful hobe.
