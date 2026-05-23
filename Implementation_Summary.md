# Implementation Summary — LakbayLokal Marketplace (Hardened Release)

**Project Name:** LakbayLokal — LGU Local Products Marketplace  
**Database:** `railway` (MySQL via MySQLi — Railway cloud for production, localhost for development)  
**Stack:** PHP 8.2 (procedural), MySQL, HTML/CSS (vanilla), JavaScript (vanilla), PHPMailer, vlucas/phpdotenv  
**Deployment:** Docker (PHP 8.2 Apache image) + Railway cloud database  
**Theme:** Lingayen, Pangasinan souvenir and tourism e-commerce platform

---

## What Changed in This (Hardened) Release vs. FR FR

| Area | FR FR | Hardened Release |
|---|---|---|
| CSRF protection | Not implemented | All 15 POST handlers protected; token-rotation strategy split by form type |
| `delete_product.php` | GET-based, raw SQL, no auth exit | POST-only, CSRF verified, prepared statement, proper redirects |
| `restock.php` | Three `die()` calls, no CSRF | No `die()`, CSRF verified, prepared statement, redirect on error |
| `add_to_cart.php` | No CSRF, raw `mysqli_query` | CSRF (no-rotate), fully prepared statements |
| `add_to_favorite.php` | No CSRF, raw queries, unsafe redirect | CSRF (no-rotate), prepared statements, whitelist-only redirect |
| `toggle_favorite.php` | No CSRF, raw queries | CSRF (no-rotate), prepared statements |
| `verify_register.php` | No CSRF on OTP form | CSRF added to include and form |
| `orders.php` approve form | Missing `csrf_field()` | Token field added |
| `products.php` forms | Missing `csrf_field()` on both forms | Token field added to favorite and cart forms |
| DB credentials | Hardcoded in `db.php` | Read from `.env` via `$_ENV` |
| `.env` in source | Live credentials committed | Credentials scrubbed; `.env.example` added; `.env` gitignored |
| `die()` calls | Present in restock and delete | Zero `die()` calls remain anywhere |

---

## 1. Project Structure

```
FR_OUT/
├── index.php                        # Public landing / home page
├── .env                             # Secrets — never commit (gitignored)
├── .env.example                     # Committed template showing required keys
├── composer.json                    # Requires phpmailer + phpdotenv
├── Dockerfile                       # PHP 8.2-apache, installs mysqli/pdo/zip, runs composer
├── config/
│   ├── db.php                       # MySQLi connection — reads all credentials from .env
│   ├── csrf.php                     # CSRF helpers: csrf_token(), csrf_field(),
│   │                                #   verify_csrf() (rotates), verify_csrf_no_rotate()
│   └── mailer.php                   # sendMail() — PHPMailer + .env SMTP credentials
├── auth/
│   ├── login.php                    # Prepared-statement login + password_verify + CSRF
│   ├── register.php                 # Registration → OTP email → session store + CSRF
│   ├── verify_register.php          # OTP verify: expiry + 5-attempt lockout + CSRF
│   ├── forgot.php                   # Dual-mode: username recovery OR password reset + CSRF
│   ├── verify_recovery.php          # OTP verify for account recovery + CSRF
│   ├── reset_password.php           # New password (blocks reuse) + CSRF
│   ├── show_username.php            # Displays recovered username after OTP verified
│   └── logout.php                   # session_destroy() + redirect
├── admin/
│   ├── dashboard.php                # Role-aware dashboard (admin vs buyer)
│   ├── admin_products.php           # CRUD products + restock + category filter + CSRF
│   ├── edit_product.php             # Product edit, MIME-validated image replace + CSRF
│   ├── approve_order.php            # Order approval + order_audit insert + CSRF
│   ├── delete_product.php           # POST-only product deletion + CSRF + prepared statement
│   └── restock.php                  # Stock-add endpoint + CSRF + prepared statement
├── user/
│   ├── catalog.php                  # Public product listing (no auth required)
│   ├── products.php                 # Auth-gated listing with favorites + category filter
│   ├── add_to_cart.php              # Cart insert, stock guard, CSRF (no-rotate)
│   ├── cart.php                     # DB-backed cart view + CSRF field on checkout form
│   ├── checkout.php                 # Order creation + stock deduction + cart clear + CSRF
│   ├── orders.php                   # Orders list (role-aware) + CSRF field on approve form
│   ├── add_to_favorite.php          # Favorites toggle + CSRF (no-rotate) + safe redirect
│   ├── toggle_favorite.php          # Favorites toggle (catalog) + CSRF (no-rotate)
│   └── tourism.php                  # Tourism areas with Leaflet.js interactive map
├── reviews/
│   ├── reviews.php                  # Review submit / edit / delete + CSRF
│   └── review_records.php           # Admin view of all reviews
├── assets/css/
│   ├── style.css                    # Global stylesheet (1 070 lines)
│   ├── images/pictures/             # Static landmark images
│   └── uploads/                     # Product image uploads (.htaccess execution guard)
└── vendor/                          # Composer-managed (gitignored; installed at deploy time)
```

---

## 2. Database Schema

| Table | Key Columns |
|---|---|
| `users` | `id`, `fullname`, `username`, `email`, `password` (bcrypt), `role` |
| `products` | `id`, `name`, `description`, `price`, `image`, `category`, `stock`, `created_at` |
| `cart` | `id`, `user_id`, `product_id`, `quantity` |
| `orders` | `id`, `user_id`, `total`, `status` (`Pending`/`Approved`), `created_at` |
| `order_items` | `id`, `order_id`, `product_id`, `quantity`, `price` |
| `order_audit` | `id`, `order_id`, `admin_id`, `action` |
| `favorites` | `id`, `user_id`, `product_id` |
| `reviews` | `id`, `user_id`, `product_id`, `rating` (1–5), `comment`, UNIQUE(`user_id, product_id`) |

---

## 3. Environment & Configuration

### `.env` (gitignored — never commit)
All secrets live here. Copy `.env.example`, fill in real values, and place the file in the project root.

```
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_FROM=your-gmail@gmail.com
MAIL_FROM_NAME=LakbayLokal

DB_HOST=kodama.proxy.rlwy.net   # or localhost for local dev
DB_USER=root
DB_PASS=your-db-password
DB_NAME=railway                 # or your locally created database
DB_PORT=47100                   # or 3306 for local dev
```

### `config/db.php`
Reads all connection parameters from `$_ENV` (populated by phpdotenv). No credentials are hardcoded. Falls back to safe defaults if a key is absent. Errors are written to the PHP error log only — no credentials or stack traces reach the browser.

### `config/csrf.php`
Four functions:

- `csrf_token()` — generates and stores a 64-hex-char token in `$_SESSION`.
- `csrf_field()` — emits a hidden `<input>` for use inside forms.
- `verify_csrf()` — validates the submitted token with `hash_equals` then **rotates** it. Used on high-value actions: login, register, checkout, approve order, edit/delete product, reviews, account recovery.
- `verify_csrf_no_rotate()` — same validation, **no rotation**. Used when a single page renders many forms simultaneously (product grid: one cart + one favorite button per card). Rotating after the first card submission would break all remaining cards on the page.

### `config/mailer.php` — `sendMail($to, $subject, $body)`
- All SMTP settings read from `getenv()`.
- 30-second per-session cooldown prevents email flooding.
- Returns `true` on success or an error string on failure.
- HTML email with plain-text `AltBody` fallback.
- STARTTLS on port 587.

---

## 4. CSRF Protection — Full Coverage Map

| Handler | Function used | Reason |
|---|---|---|
| `auth/login.php` | `verify_csrf()` | Rotates — single form |
| `auth/register.php` | `verify_csrf()` | Rotates — single form |
| `auth/verify_register.php` | `verify_csrf()` | Rotates — single form |
| `auth/forgot.php` | `verify_csrf()` | Rotates — single form |
| `auth/verify_recovery.php` | `verify_csrf()` | Rotates — single form |
| `auth/reset_password.php` | `verify_csrf()` | Rotates — single form |
| `user/add_to_cart.php` | `verify_csrf_no_rotate()` | No rotate — one form per product card |
| `user/add_to_favorite.php` | `verify_csrf_no_rotate()` | No rotate — one form per product card |
| `user/toggle_favorite.php` | `verify_csrf_no_rotate()` | No rotate — one form per product card |
| `user/checkout.php` | `verify_csrf()` | Rotates — single form |
| `admin/admin_products.php` | `verify_csrf()` | Rotates — add + restock forms |
| `admin/edit_product.php` | `verify_csrf()` | Rotates — single form |
| `admin/delete_product.php` | `verify_csrf()` | Rotates — POST-only delete |
| `admin/restock.php` | `verify_csrf()` | Rotates — single form |
| `admin/approve_order.php` | `verify_csrf()` | Rotates — single form |
| `reviews/reviews.php` | `verify_csrf()` | Rotates — submit + edit forms |

---

## 5. Authentication & Authorization

### Login (`auth/login.php`)
Prepared statement lookup by username; `password_verify()` against bcrypt hash. Sets `$_SESSION["user_id"]` and `$_SESSION["role"]`; redirects to `admin/dashboard.php`.

### Registration (`auth/register.php`)
1. Username and email uniqueness both checked via prepared statements.
2. Password hashed immediately with `PASSWORD_DEFAULT` (bcrypt).
3. 6-digit OTP with 10-minute expiry emailed via `sendMail()`.
4. OTP is never shown on screen.
5. Redirects to `verify_register.php`.

### OTP Verification (`auth/verify_register.php`)
- Expiry enforced: session destroyed and user redirected on timeout.
- 5-attempt lockout: session destroyed on too many wrong codes.
- Resend: new OTP, reset counter, new email.
- On correct code: prepared statement `INSERT INTO users`.

### Account Recovery (`auth/forgot.php` → outcome pages)
Dual-mode tab UI — "Forgot Username" or "Forgot Password":

- Email lookup via prepared statement; 15-minute OTP emailed.
- `verify_recovery.php` checks expiry then routes to `show_username.php` or `reset_password.php`.
- Password reset blocks reuse of the current password via `password_verify` check before update.
- All session recovery keys destroyed after use.

---

## 6. Security Assessment

| Item | Status |
|---|---|
| Login SQL | ✅ Prepared statement |
| Registration username/email checks | ✅ Prepared statements |
| Registration INSERT | ✅ Prepared statement |
| OTP on screen | ✅ Removed — emailed only |
| OTP expiry | ✅ 10 min (register) / 15 min (recovery) |
| OTP resend rate-limit | ✅ 30-second session cooldown |
| Password hashing | ✅ bcrypt |
| Password reuse prevention | ✅ Checked on reset |
| Account recovery queries | ✅ Prepared statements |
| Admin product CRUD | ✅ Prepared statements |
| Review CRUD | ✅ Prepared statements |
| Cart / favorites | ✅ Prepared statements |
| Image upload validation | ✅ MIME type + extension whitelist (jpg/png/webp) via `finfo` |
| Stock re-validated at checkout | ✅ Server-side before order creation |
| Order audit trail | ✅ `order_audit` table |
| Upload directory execution guard | ✅ `.htaccess` present |
| CSRF — all POST handlers | ✅ 15/15 covered (rotate or no-rotate as appropriate) |
| `die()` calls | ✅ Zero remaining — all replaced with redirects + `error_log()` |
| DB credentials in source | ✅ Fully in `.env`, read via `$_ENV` |
| `.env` in version control | ✅ Gitignored; `.env.example` committed instead |
| `vendor/` in version control | ✅ Gitignored; installed by Dockerfile at deploy time |

---

## 7. Shopping & Order Flow

- **Cart**: DB-backed (`cart` table), persistent across sessions.
- **Add to cart**: stock checked before insert; quantity capped at available stock; CSRF protected.
- **Checkout**: stock re-validated server-side → order INSERT → `order_items` INSERT + stock decrement → cart DELETE.
- **Orders**: role-aware (buyers see own; admins see all with username JOIN).
- **Approval**: admin POSTs to `approve_order.php` (CSRF verified); sets `status = 'Approved'`; writes to `order_audit`.

---

## 8. Deployment

### Local Development (XAMPP)
Create a local `railway` database in phpMyAdmin, import your schema/data, and set `.env` to:
```
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_PORT=3306
```
This avoids latency from the cloud database during development.

### Production (Docker + Railway)
```dockerfile
FROM php:8.2-apache
RUN apt-get install -y git zip unzip libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip
RUN a2enmod rewrite
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY . /var/www/html/
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader
EXPOSE 80
```
Set Railway environment variables to the production DB and SMTP credentials. The `.env` file is not included in the Docker image — credentials come from Railway's variable injection at runtime.

---

## 9. Remaining Recommendations

- **Rotate credentials**: the Railway DB password and Gmail App Password were present in an earlier zip. Both should be regenerated before any public deployment.
- **Post-login redirect**: currently sends all users to `admin/dashboard.php`; a neutral `/dashboard.php` would improve buyer UX.
- **HTTPS**: enforce TLS in production (Railway provides this automatically on custom domains).
- **Session hardening**: consider `session_regenerate_id(true)` on login to prevent session fixation.