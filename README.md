# LakbayLokal Marketplace

> An LGU-backed online marketplace for authentic souvenir products from **Lingayen, Pangasinan**.

LakbayLokal connects customers to local souvenir shops in Lingayen — browse, order, and review locally made delicacies, handicrafts, and cultural keepsakes while supporting small businesses and the town's tourism identity.

---

## Features

### For Guests (no account required)
- Browse the public product catalog
- View Lingayen tourism areas on an interactive Leaflet.js map
- Register for an account with real email OTP verification

### For Buyers
- Add products to a persistent database-backed cart
- Mark favorites (sorted to the top of listings)
- Place orders with server-side stock validation and automatic deduction
- Track order status (Pending / Approved)
- Submit, edit, and delete product reviews (one per product)
- Recover forgotten username or reset password via email OTP

### For Admins
- Add, edit, delete, and restock products with MIME-validated image upload
- Approve customer orders (with audit trail logged to `order_audit`)
- View all orders across all users
- View all submitted product reviews

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2 (procedural) |
| Database | MySQL via MySQLi |
| Frontend | HTML5, CSS3 (vanilla), JavaScript (vanilla) |
| Maps | Leaflet.js (CDN) |
| Email | PHPMailer 7.x + Gmail SMTP |
| Config | vlucas/phpdotenv v5 |
| Passwords | PHP `password_hash()` / `password_verify()` (bcrypt) |
| CSRF | Custom token helpers in `config/csrf.php` |
| Sessions | PHP native sessions |
| Testing | PHPUnit 11.x |
| Deployment | Docker (php:8.2-apache) + Railway (MySQL) |

---

## Requirements

### For Local Development
- PHP 8.2 or higher
- MySQL 5.7 or higher
- [Composer](https://getcomposer.org/)
- A local server: [XAMPP](https://www.apachefriends.org/), [Laragon](https://laragon.org/), or Docker

### For Docker Deployment
- [Docker](https://www.docker.com/) installed
- A Railway (or any external MySQL) database provisioned

---

## Installation

### Option A — Local (XAMPP / Laragon)

**1. Extract the project**

Place the project folder inside your server's web root:
```
# XAMPP
C:\xampp\htdocs\FR_OUT\

# Laragon
C:\laragon\www\FR_OUT\
```

**2. Install Composer dependencies**
```bash
cd /path/to/FR_OUT
composer install
```

**3. Create the database**

Open phpMyAdmin, create a database (e.g. `railway`), then run the SQL in the **Database Schema** section below.

**4. Configure `.env`**

Copy `.env.example` to `.env` and fill in your values:
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_FROM=your-email@gmail.com
MAIL_FROM_NAME=LakbayLokal

DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=railway
DB_PORT=3306
```

> **Gmail App Password:** Google Account → Security → 2-Step Verification → App passwords. Use this, not your regular Gmail password.

**5. Open the site**
```
http://localhost/FR_OUT/
```

---

### Option B — Docker

**1. Build the image**
```bash
docker build -t lakbaylokal .
```

**2. Run the container**

Pass all credentials as environment variables — do not bake them into the image:
```bash
docker run -p 8080:80 \
  -e MAIL_HOST=smtp.gmail.com \
  -e MAIL_PORT=587 \
  -e MAIL_USERNAME=your-email@gmail.com \
  -e MAIL_PASSWORD="your-app-password" \
  -e MAIL_FROM=your-email@gmail.com \
  -e MAIL_FROM_NAME=LakbayLokal \
  -e DB_HOST=your-railway-host \
  -e DB_USER=root \
  -e DB_PASS=your-db-password \
  -e DB_NAME=railway \
  -e DB_PORT=47100 \
  lakbaylokal
```

**3. Open the site**
```
http://localhost:8080/
```

---

## Running Tests

The project includes a PHPUnit test suite covering all major features. Tests run against a separate `railway_test` database so your real data is never touched.

### Setup

**1. Create the test database**

Open phpMyAdmin, create a new database called `railway_test`, then run the same **Database Schema** SQL below on it.

**2. Install dev dependencies** (if not already done)
```bash
composer install
```

**3. Run the tests**
```bash
./vendor/bin/phpunit
```

Expected output:
```
OK (30 tests, 45 assertions)
```

### Test Coverage

| File | What it tests |
|---|---|
| `tests/CartTest.php` | Cart prepared statement, stock deduction, oversell blocked, order insert, cart clear after checkout |
| `tests/AuthTest.php` | Password verify, wrong password rejected, duplicate username/email blocked, default role |
| `tests/ProductTest.php` | Product insert, update, delete, restock, stock cannot go negative |
| `tests/OrderTest.php` | Default Pending status, total stored correctly, admin approval, audit trail recorded, no re-approval |
| `tests/ReviewTest.php` | Review insert, duplicate blocked by UNIQUE constraint, rating range 1–5, update, delete |
| `tests/FavoriteTest.php` | Favorite add, remove, duplicate blocked, toggle add, toggle remove |

---

## Database Schema

Run the following SQL in phpMyAdmin or your MySQL client:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','buyer') DEFAULT 'buyer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    category VARCHAR(100),
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('Pending','Approved') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL
);

CREATE TABLE order_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    admin_id INT NOT NULL,
    action VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_product (user_id, product_id)
);
```

---

## Creating an Admin Account

Run this PHP snippet once to generate a bcrypt hash for your chosen password:
```php
<?php echo password_hash('your-password-here', PASSWORD_DEFAULT); ?>
```

Then insert the admin user directly in MySQL:
```sql
INSERT INTO users (fullname, username, email, password, role)
VALUES ('LGU Admin', 'admin', 'admin@lakbaylokal.ph', '<paste-hash-here>', 'admin');
```

---

## Directory Structure

```
FR_OUT/
├── index.php               # Landing page (public)
├── .env                    # Secrets — gitignored, never commit
├── .env.example            # Committed template showing required keys
├── composer.json           # Composer dependencies (phpmailer, phpdotenv, phpunit)
├── phpunit.xml             # PHPUnit configuration
├── Dockerfile              # PHP 8.2 Apache container
├── config/
│   ├── db.php              # MySQLi connection — reads all values from .env
│   ├── csrf.php            # CSRF helpers: token generation, field output, verification
│   └── mailer.php          # PHPMailer sendMail() helper
├── auth/                   # Login, register, OTP verify, forgot, reset, logout
├── admin/                  # Product CRUD, restock, order approval, review records
├── user/                   # Catalog, products, cart, checkout, orders, favorites, tourism
├── reviews/                # Review submit/edit/delete and admin records view
├── tests/
│   ├── TestCase.php        # Base test class with MySQLi connection setup
│   ├── CartTest.php        # Cart and checkout tests
│   ├── AuthTest.php        # Authentication and registration tests
│   ├── ProductTest.php     # Product CRUD and stock tests
│   ├── OrderTest.php       # Order flow and approval tests
│   ├── ReviewTest.php      # Review CRUD and constraint tests
│   └── FavoriteTest.php    # Favorites toggle and duplicate tests
├── assets/css/
│   ├── style.css           # Global styles (1 070 lines)
│   ├── images/             # Static landmark photos
│   └── uploads/            # Admin-uploaded product images (.htaccess execution guard)
└── vendor/                 # Composer packages — gitignored, installed at deploy time
```

---

## User Roles

| Role | Access |
|---|---|
| **Guest** | Landing page, public catalog, tourism map, registration |
| **Buyer** | All guest access + authenticated products, cart, checkout, orders, reviews, favorites, account recovery |
| **Admin** | All buyer access + product CRUD, restock, order approval, review records |

---

## Account Recovery

Users who forget their credentials can use the **"Forgot Username or Password?"** link on the login page:

1. Enter the email address linked to the account.
2. Choose **Forgot Username** or **Forgot Password**.
3. A 6-digit code is emailed (valid for 15 minutes).
4. Enter the code on the verification page (max 5 attempts; code expires on timeout or lockout).
5. Either the username is revealed, or a new password can be set — reusing the old password is blocked.

---

## Security Notes

All major attack surfaces are addressed in this release:

- **SQL injection** — every database query uses prepared statements with bound parameters.
- **CSRF** — all 15 POST handlers are protected via `config/csrf.php`. Handlers on pages with multiple simultaneous forms (product grid) use `verify_csrf_no_rotate()` to avoid invalidating sibling forms on the same page.
- **Password security** — bcrypt hashing throughout; password reuse blocked on reset.
- **Image uploads** — MIME type checked via `finfo` and extension whitelisted to jpg/png/webp. Upload directory has an `.htaccess` execution guard.
- **OTP security** — codes are emailed only (never shown on screen), expire after 10–15 minutes, and lock out after 5 wrong attempts. Resend is rate-limited to 30 seconds per session.
- **Error exposure** — no `die()` calls remain; all errors redirect cleanly or write to the PHP error log only.
- **Credentials** — all secrets live in `.env` (gitignored); a `.env.example` template is committed instead.
- **Transactional checkout** — stock check uses `FOR UPDATE` row lock inside a database transaction, preventing overselling under concurrent load.

---

## Email Configuration Notes

- Gmail requires **2-Step Verification** enabled and an **App Password** — not your regular account password.
- A 30-second cooldown is enforced between OTP resend requests.
- Mail errors are written to PHP's error log; users see a generic failure message.

---

## Local vs. Production Database

For local development, point `.env` at `localhost` / port `3306` to avoid the latency of the Railway cloud proxy. Switch back to the Railway credentials only when deploying. The app reads all connection values from `.env` at runtime — no code changes needed to switch environments.

---

## Credits

- Built for the **Lingayen, Pangasinan** LGU souvenir marketplace initiative.
- Map tiles via [OpenStreetMap](https://www.openstreetmap.org/) through [Leaflet.js](https://leafletjs.com/).
- Email delivery via [PHPMailer](https://github.com/PHPMailer/PHPMailer).
- Environment config via [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv).
- Automated testing via [PHPUnit](https://phpunit.de/).

---

## License

This project is intended for LGU and academic use. All rights reserved by the development team.