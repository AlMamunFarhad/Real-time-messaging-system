# Real-time Messaging System Developer Guide

This guide is written so that a Laravel developer can integrate this messaging system into another project by following the document alone. The login/auth system is not bundled as a requirement here; you can keep using your existing auth, guards, middleware, layouts, and route naming.

## 1. What You Are Getting

This system includes:

- 1-to-1 private conversation support
- Real-time messaging via Laravel Reverb
- Unread badge counter
- Read status tracking
- Polymorphic participants and sender support
- Admin-side conversation panel
- User-side popup message panel
- Online status heartbeat/check endpoints
- Optional API structure for external or mobile extension

## 2. Best Use Case

This system is a good fit if your project already has:

- an existing login system
- chat between admin and user
- support or inquiry messaging
- internal inbox-style messaging
- a Blade-based dashboard

If your project uses React, Vue, or Inertia, the backend part can stay mostly the same, but the UI layer will need customization.

## 3. High-level Architecture

The system works in 4 layers:

1. Database layer  
   `conversations`, `conversation_participants`, `messages`

2. Business logic layer  
   module controllers, models, helper, and event

3. Realtime layer  
   Laravel Reverb + Echo + private broadcast channels

4. UI layer  
   admin chat page + message icon + user popup panel

## 4. Core Files You Need To Understand

If a developer reads these files first, they can understand the full system much faster:

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

The easiest way to move this system into another project is:

1. Copy the messaging module
2. Install the required packages
3. Run the migrations
4. Configure broadcasting and Reverb
5. Adjust auth mapping
6. Adjust channel authorization
7. Place the UI component
8. Run end-to-end testing

## 6. Dependencies

Your target project should have at least:

- PHP `8.2+`
- Laravel `12.x`
- Node.js
- Vite
- a database

Required packages:

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
- messaging-related channel rules from `routes/channels.php`

Project-specific pieces that usually need adaptation:

- admin routes in `routes/web.php`
- admin layout usage such as `x-admin-layout`
- guard names
- model namespaces
- route names

## 8. Database Design

### `conversations`

- stores the conversation shell
- currently defaults `type` to `private`

### `conversation_participants`

- links users/admins with a conversation
- supports polymorphic participants
- stores `last_read_at`
- stores `joined_at`

### `messages`

- stores each message row
- sender is polymorphic
- supports text and file
- stores `read_at`
- has a soft-delete style flag `is_deleted`

## 9. Auth Model

The current implementation assumes two auth guards:

- `admin`
- `web`

The mapping currently lives in:

- `Modules/Messaging/app/Helpers/AuthParticipant.php`

Default type mapping:

- `admin` -> `App\\Models\\Admin`
- `web` -> `App\\Models\\User`

### If your project uses the same guards

Integration will be much easier. In most cases, only route and layout naming need adjustment.

### If your project uses only one guard

Start by changing:

- `Modules/Messaging/app/Helpers/AuthParticipant.php`

Typical change:

```php
protected static $guards = ['web'];
```

Then update the `type()` method with your own model mapping.

### If your project uses custom guards

For example:

- `customer`
- `staff`

Then you need to update:

- `AuthParticipant.php`
- `Modules/Messaging/routes/web.php`
- `routes/channels.php`
- any place where route middleware uses `auth:admin,web`

## 10. Route Overview

### Messaging module web routes

Source:

- `Modules/Messaging/routes/web.php`

Important routes:

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

Important routes:

- `GET /admin/messages`
- `GET /admin/messages/conversation`
- `GET /user/messages/conversation`
- `GET /admin/users/list`

These routes are mainly for UI convenience, not for the core backend module itself.

## 11. Broadcasting Design

### Event

- `Modules/Messaging/app/Events/MessageSent.php`

The event broadcasts to multiple private channels:

- `conversation.{conversationId}`
- `user.admin.{id}`
- `user.user.{id}`

### Channel authorization

Source:

- `routes/channels.php`

For the realtime system to work reliably, this file must align with your auth system.

If there is a guard mismatch here, you may see:

- unread badge sync failures
- live chat update failures
- `403` errors on private channels

## 12. Frontend Realtime Setup

Frontend Echo setup exists in:

- `resources/js/echo.js`
- `resources/js/bootstrap.js`
- `resources/js/app.js`

The current setup uses Reverb. Make sure you have:

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

Production build:

```bash
npm run build
```

## 13. Step-by-step Integration Guide

### Step 1. Add the module

Copy `Modules/Messaging/` into the root of your target project.

Then run:

```bash
composer dump-autoload
```

### Step 2. Check that the module is active

Make sure:

- `module.json` exists
- `modules_statuses.json` has the module enabled
- `nwidart/laravel-modules` is installed

### Step 3. Run migrations

```bash
php artisan module:migrate Messaging
```

Fallback:

- move the files from `Modules/Messaging/database/migrations` into the main migration folder and run `php artisan migrate`

### Step 4. Configure broadcasting

Check these files:

- `config/broadcasting.php`
- `config/reverb.php`
- `.env`

### Step 5. Add channel rules

Add messaging-related private channel authorization rules to `routes/channels.php`.

Minimum rule concept:

- the current logged-in user can access their own personal channel
- a user can only access their own conversation channel

### Step 6. Adapt `AuthParticipant`

File:

- `Modules/Messaging/app/Helpers/AuthParticipant.php`

This file is the auth bridge for the whole messaging system.

If you do not adapt it correctly, you may get:

- message load failures
- send failures
- wrong badge counts
- incorrect admin/user detection

### Step 7. Place the UI component

In your navbar or topbar:

```blade
<x-message-icon />
```

This component can be used for both user and admin, but the route and layout dependencies must be correct.

### Step 8. Wire the admin chat page

Current repo admin page:

- `Modules/Messaging/resources/views/chat/admin.blade.php`

To use it, make sure:

- the admin route exists
- the admin layout exists
- the user list endpoint exists
- admin auth middleware is correct

### Step 9. Wire the user popup route

The current repo uses:

- `user.messages.conversation`

Make sure your app has this route or an equivalent one.

### Step 10. Build and test

```bash
php artisan optimize:clear
php artisan serve
php artisan reverb:start
npm run dev
```

## 14. Quick Verification Checklist

After integration, verify these 10 points:

1. Logged-in users can see the message icon
2. Admin can see the message icon
3. Sending a message inserts a row into `messages`
4. A new conversation fills `conversations` and `conversation_participants`
5. Admin unread badge count updates
6. User unread badge count updates
7. Admin can select a user and open chat
8. User can open admin chat
9. Messages arrive in realtime without page refresh
10. Marking messages as read reduces the counter

## 15. Common Customization Points

When moving this system to another project, the most common changes are:

- guard names
- model namespaces
- route names
- admin layout/component name
- admin user list source
- message icon placement
- popup design
- file storage path

## 16. Troubleshooting

### Problem: unread badge is not working

Check:

- whether `messages.conversations` is authenticated
- whether the component poll request returns `200`
- whether the auth guard is detected correctly
- whether `AuthParticipant` returns the correct model

### Problem: realtime message is not arriving

Check:

- whether Reverb is running
- whether the browser console shows websocket errors
- whether channel authorization is returning `403`
- whether the event is being dispatched
- whether `BROADCAST_CONNECTION=reverb` is set

### Problem: admin side works, user side does not

Check:

- whether `user.messages.conversation` exists
- whether the user popup component branch uses the correct route
- whether the `web` guard is detected

### Problem: user side works, admin side does not

Check:

- whether the `admin` guard is detected
- whether `admin.messages` exists
- whether the component is rendered in the admin layout
- whether admin authorization in `routes/channels.php` is correct

### Problem: `401` or `403`

Usual root causes:

- guard mismatch
- middleware mismatch
- channel auth mismatch
- session or CSRF issue

## 17. Recommended Refactor If You Want To Reuse This Everywhere

If you want to raise this to reusable package-level quality, a good next step would be:

1. make `AuthParticipant` config-driven
2. make route names config-driven
3. make admin/user model mapping config-driven
4. split the message icon into smaller partials/components
5. move inline JS logic into external files
6. split admin and user UI into separate components

These improvements will make future integrations much faster.

## 18. Recommended Minimum Files To Edit In A New Project

If you want the minimum edit path, start with these files:

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

This messaging system is not fully plug-and-play, but it is strongly reusable. The main work is aligning the auth layer, route naming, and UI placement. Once those 3 parts are aligned, the backend logic, unread counting, conversation structure, and realtime flow can work reliably in another project as well.

If you want, the next useful addition would be an `INTEGRATION_CHECKLIST.md` or `COPY_FILES_LIST.md` for developer handoff.
