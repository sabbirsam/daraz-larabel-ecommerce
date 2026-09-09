# Laravel Ecommerce Build Guide (Daraz style store)

This is your single reference document. Keep it open in one tab while you work in the AI editor. Part 10 contains the master prompt you paste into the AI editor to start the actual build.

---

## Part 1. What you already have

You said PHP and XAMPP are already installed. That covers your local web server (Apache) and your database (MySQL) plus phpMyAdmin. Laravel does not run inside XAMPP the way a WordPress plugin runs inside WordPress. Laravel is its own standalone PHP application. XAMPP only supplies two things it needs: PHP itself and MySQL. So from here you need three more tools before touching Laravel.

Check your PHP version first. Open a terminal and run:

```
php -v
```

Laravel 11 and 12 need PHP 8.2 or higher. If your XAMPP PHP is older, download a newer XAMPP build or install a separate PHP 8.3 and switch your system path to point at it.

---

## Part 2. Install Composer

Composer is the package manager for the entire PHP world, the same role npm plays for JavaScript. Laravel itself is installed and updated through Composer.

1. Go to getcomposer.org and download Composer Setup for Windows.
2. Run it. It will ask for the path to your php.exe, usually something like C colon backslash xampp backslash php backslash php.exe. Point it there.
3. Finish the installer, open a fresh terminal, and confirm with:

```
composer -V
```

---

## Part 3. Install Node.js and npm

Laravel's frontend build tool, Vite, needs Node and npm to compile your CSS and JavaScript.

1. Go to nodejs.org and download the LTS version.
2. Install it with default options.
3. Confirm with:

```
node -v
npm -v
```

---

## Part 4. Install Git

You will want version control from day one, both so the AI editor can track every change it makes and so you never lose work.

1. Download Git for Windows from git dash scm dot com.
2. Install with default options.
3. Confirm with:

```
git --version
```

---

## Part 5. Install your AI code editor

Pick one of these. Since you already use Claude, either of the first two options will feel the most natural.

**Option A, Claude Code inside VS Code or your terminal.** Install VS Code from code.visualstudio.com, then install the Claude Code extension, or install Claude Code as a terminal tool if you prefer working from the command line. Claude Code can read your whole project folder, write files directly, run artisan commands for you, and keep a running log of what it did.

**Option B, Cursor.** Download Cursor from cursor.sh. It is a fork of VS Code with AI editing built in and works well with large multi file projects like this one.

Either way, the workflow is the same: open your project folder in the editor, paste the master prompt from Part 10 into the chat panel, and let the AI start creating files.

---

## Part 6. Create the Laravel project

Open a terminal inside your XAMPP htdocs folder, for example C colon backslash xampp backslash htdocs, and run:

```
composer create-project laravel/laravel ecommerce
cd ecommerce
```

This downloads the latest stable Laravel release into a folder named ecommerce.

Install the frontend dependencies:

```
npm install
```

---

## Part 7. Connect the database

1. Open the XAMPP control panel and start Apache and MySQL.
2. Open phpMyAdmin (usually localhost slash phpmyadmin).
3. Create a new database, for example daraz_clone. Leave the collation as the default.
4. Open the .env file inside your Laravel project folder and set:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=daraz_clone
DB_USERNAME=root
DB_PASSWORD=
```

5. Generate your app key and run the first migration to confirm the connection works:

```
php artisan key:generate
php artisan migrate
```

If that runs without errors, your database connection is good.

---

## Part 8. Run the project

```
php artisan serve
```

Open the address it prints, normally http colon slash slash 127.0.0.1 colon 8000, and you should see the Laravel welcome page. At this point the base project is ready and everything else happens through the AI editor.

Optional but recommended: instead of always typing php artisan serve, set up a proper virtual host in XAMPP so the site runs at something like http colon slash slash ecommerce dot local. This matters once you start testing payment gateway callbacks, since some gateways behave better with a real hostname than with 127.0.0.1. You can ask the AI editor to help you configure this once the project exists.

---

## Part 9. The technology stack this guide is built around

You do not need to memorize this, the AI prompt in Part 10 already specifies it, but here is what each piece does and why it was chosen for a Daraz style store.

**Backend:** Laravel 11 (or the latest 12 if it is stable when you start), PHP 8.3.

**Admin dashboard:** Filament PHP. This is the single biggest time saver for this project. It gives you a complete, modern admin panel for managing products, categories, orders, customers, coupons, and settings, generated mostly from your database models instead of you hand building dozens of admin screens. This is exactly the "admin dashboard" part of your request.

**Storefront frontend:** Blade templates with Tailwind CSS and Alpine.js, since you already know these from your WordPress and SCSS work, plus Livewire for interactive pieces like the cart drawer and live search, so you are not forced to learn a separate React or Vue app just for the customer facing site.

**Authentication:** Laravel Breeze for the customer facing login, register, and password reset screens. Filament has its own separate admin login, so admins and customers never share the same login system.

**Roles and permissions:** spatie slash laravel dash permission, so you can have roles like admin, staff, and customer with different access levels.

**Shopping cart:** a custom cart service built on the session and database, since your product variants (color, size) and guest checkout flow need custom logic anyway. The AI prompt asks for this to be built as a proper Cart service class rather than a quick hack.

**Payments:** SSLCommerz for local Bangladeshi cards, mobile banking, and bKash and Nagad support in one integration, since that matches the Daraz style checkout you are copying, plus Stripe as a second gateway if you ever want international cards. Both are wired through a single PaymentGateway interface so adding a third gateway later does not require rewriting checkout.

**Mobile OTP:** phone verification during registration and at checkout, sent through an SMS gateway API (a local BD SMS provider or Twilio Verify, whichever you already have an account with). Wired as its own OtpService class so it is easy to swap providers later.

**Images:** spatie slash laravel dash medialibrary for product photo galleries, with automatic thumbnail generation.

**Search and filters:** Laravel Scout with the database driver to start (upgradeable to Meilisearch later if the catalog grows large), used to power the search bar and the sidebar filters you saw in the Daraz bag and battery listing pages.

**Reviews and ratings:** a custom Review model tied to verified purchases, matching what you saw on the Imou camera product page.

---

## Part 10. Database plan (what tables you will end up with)

Give this list to the AI as the target schema. It will generate the actual migrations, but knowing the shape helps you review its work.

- users (customers), with phone_verified_at for OTP status
- admins, managed separately through Filament
- categories, self referencing for parent and child categories like the sidebar tree you saw on the bags page
- brands
- products, with SKU, price, sale price, stock, description
- product_images
- product_variants (color, size, and their own price and stock overrides)
- attributes and attribute_values, to power the sidebar filters (brand, size, color, price range, rating)
- carts and cart_items
- addresses, tied to users, matching the shipping and billing block you saw at checkout
- orders and order_items
- payments, storing gateway, transaction id, and status
- coupons
- reviews and review_images
- wishlists
- otp_codes, storing the code, phone, expiry, and attempt count
- settings, for store wide configuration inside Filament

---

## Part 11. Development phases (the roadmap)

This is the order the AI prompt will follow. Each phase should end with something you can actually click through in the browser before moving to the next one.

1. Project skeleton, authentication, and the Filament admin panel shell
2. Category, brand, and product management inside Filament, including variants and images
3. Public storefront home page, category pages, product listing with filters, and the single product page
4. Cart and wishlist
5. Checkout flow, addresses, and order placement
6. Payment gateway integration (SSLCommerz first, Stripe second)
7. Mobile OTP for registration and checkout
8. Order management, order status updates, and customer order history
9. Reviews and ratings
10. Coupons, search, and final polish

---

## Part 12. Design notes pulled from your Daraz screenshots

Give the AI these specifics so the storefront actually looks like the reference instead of a generic Tailwind starter.

- Primary brand color is a warm orange, roughly hex F85606 to FF6600, used for the top header bar, buttons, sale badges, and prices
- White content area with light gray (roughly F5F5F5) page background
- Top header: logo on the left, a wide search bar in the center, and a small text link row above it for save more on app, become a seller, help and support, and account
- Category listing pages use a left sidebar with collapsible filter groups (category, brand, size, price range, rating, color) and a product grid on the right, four cards per row on desktop
- Each product card shows the image, a colored badge for choice or best price, the price in orange, a struck through original price with a percent off, a sold count, and a star rating
- The single product page has a left image gallery with thumbnails, and a right column with price, quantity selector, buy now and add to cart buttons, delivery options box, and a seller info box
- Below the product info is a full width details section, then a specifications table, then a ratings summary with a star breakdown bar chart, then individual reviews with customer photos
- The cart page lists items grouped by seller shop, with quantity steppers and a right hand order summary box showing subtotal, shipping, and a proceed to checkout button
- The checkout shipping page has shipping and billing details with an edit link, a delivery method radio choice with the guaranteed delivery date, and the same right hand order summary pattern continuing through to payment

---

## Part 13. The master AI prompt

Copy everything inside the box below into your AI code editor as the very first message once your empty Laravel project is created and running. It is written so the AI keeps a running log of progress, which solves your requirement about being able to walk away for a few days and pick back up without losing track.

```
You are building a full ecommerce web application in Laravel for me, styled after Daraz (the Bangladeshi ecommerce marketplace). I have an empty Laravel project already created and connected to a local MySQL database through XAMPP. Do not start over, build on top of what exists.

TECH STACK (use these, do not substitute without asking me first):
- Laravel, latest stable version, PHP 8.3
- Filament PHP for the entire admin dashboard
- Blade templates, Tailwind CSS, and Alpine.js for the public storefront
- Livewire for interactive storefront pieces (cart drawer, live search, quantity updates)
- Laravel Breeze for customer authentication (separate from the Filament admin login)
- spatie/laravel-permission for roles (admin, staff, customer)
- spatie/laravel-medialibrary for product images
- Laravel Scout (database driver) for search
- SSLCommerz for the primary payment gateway, with the payment logic wrapped in an interface so Stripe can be added later as a second gateway without touching checkout code
- An OTP service class for phone verification at registration and at checkout, using an SMS gateway API, written so the provider can be swapped later
- Git, with a commit after every completed feature

DESIGN DIRECTION:
Match the Daraz look. Primary brand color is a warm orange around hex F85606. Header has a logo, a wide central search bar, and a small account and help link row above it. Category pages use a left filter sidebar (category tree, brand, size, price range, rating, color) with a four column product grid on desktop. Product cards show image, a colored badge for promotions, orange price with a struck through original price and percent off, sold count, and star rating. The single product page has an image gallery on the left and a buy box on the right with quantity selector, buy now and add to cart buttons, and a delivery estimate box. The cart and checkout pages use a two column layout with items on the left and an order summary box on the right showing subtotal, shipping, and a total. Keep the storefront clean, white content areas on a light gray page background, and reuse this same layout pattern (content left, summary or filters right) across cart, checkout, and category pages.

DATABASE SHAPE (create these as proper migrations with correct foreign keys, not a rough draft):
users, admins, categories (self referencing for parent and child), brands, products, product_images, product_variants, attributes, attribute_values, carts, cart_items, addresses, orders, order_items, payments, coupons, reviews, review_images, wishlists, otp_codes, settings.

FEATURE LIST, IN THIS ORDER (do not skip ahead, finish and let me test each phase before starting the next):
1. Project skeleton, Breeze customer auth, Filament admin panel installed and reachable at /admin with its own login
2. Category and brand management in Filament, then full product management in Filament including variants, attributes, stock, pricing, and image galleries
3. Public storefront: home page, category listing pages with the sidebar filters, single product page with gallery, specs, and reviews section
4. Cart (guest and logged in) and wishlist
5. Checkout: address form, delivery method choice, order summary, order creation
6. SSLCommerz payment integration, sandbox mode first, with a success, fail, and cancel page
7. Mobile OTP: send code, verify code, gate registration and gate checkout for guest phone numbers behind verification
8. Order management in Filament (status changes, order detail view) and an order history page for customers
9. Reviews and star ratings tied to verified purchases
10. Coupons, search bar with live results, and final responsive polish across mobile and desktop

PROGRESS TRACKING, THIS IS MANDATORY, FOLLOW IT EXACTLY:
Create and maintain a file at the project root named PROGRESS.md. This file is how I track the project across sessions that may be days apart, so treat it as seriously as the code itself.

At the very start of every session, before writing any code, read PROGRESS.md if it exists and tell me a short summary of where things stand before continuing.

The file must always contain these sections, kept up to date:

GOAL
A one paragraph description of the overall project, written once and only changed if the scope changes.

PHASE STATUS
A checklist of the ten numbered features above, each marked as done, in progress, or not started.

CURRENT SESSION LOG
For every work session, add a dated entry with: what you set out to do, what you actually finished, any decisions you made and why, and any problem you hit and how you solved it or worked around it.

WHAT IS LEFT
A running bullet list of everything still needed to finish the current phase and reach a working state, updated at the end of every session so nothing is forgotten.

NEXT STEP
One or two sentences saying exactly what to do the moment work resumes, written so specifically that even if I read nothing else in this file I know where to pick up.

Update PROGRESS.md before ending every response where you made code changes, not just at the end of a whole phase. Also make a git commit after every meaningful change with a clear commit message, so the commit history and PROGRESS.md tell the same story.

Ask me before making any architecture decision that would be expensive to reverse later, such as switching the frontend approach, changing the payment gateway, or restructuring the database in a way that breaks existing data.

Start now with feature 1 from the feature list.
```

---

## Part 14. How your day to day sessions will actually go

Once the master prompt is in and the AI starts, here is the loop you will repeat:

1. Open the project in your AI editor.
2. If this is a new session after a break, just say "check PROGRESS.md and continue" and the AI will read the log and tell you exactly where things stand before writing anything.
3. Test whatever the AI just built in your browser, on both the storefront and the /admin panel, before asking for the next feature.
4. If something looks wrong, describe what you see versus what you expected, rather than guessing at the code yourself, since the AI has full context of the files it just wrote.
5. When a phase feels solid, tell the AI to move to the next numbered feature.

That is the entire loop from an empty XAMPP install to a working Daraz style store with an admin dashboard, cart, checkout, payments, and mobile OTP.





Issues - found 

That error is a classic MySQL/MariaDB + Laravel mismatch, not a mistake you made. It's almost always caused by an older MariaDB version (WAMP64 often ships one) combined with the `utf8mb4` charset Laravel uses by default. `utf8mb4` uses up to 4 bytes per character, so a `varchar(255)` unique index (like the one on `email`) needs 255 × 4 = 1020 bytes, but the older InnoDB row format only allows 1000 bytes for a key. Easy fix, no data lost since nothing exists yet.

## Fix: tell Laravel to use a shorter default string length for indexes

1. Open `app/Providers/AppServiceProvider.php`.
2. Find the `boot()` method and add one line, plus the import at the top.

```php
use Illuminate\Support\Facades\Schema;

public function boot(): void
{
    Schema::defaultStringLength(191);
}
```

3. Save the file.

## Then reset and re-run the migration

Since the `migrations` table got created before the failure, do a fresh run instead of a plain migrate:

```
php artisan migrate:fresh
```

That drops any half created tables and runs everything from a clean slate. You should now see all the default tables (`users`, `password_reset_tokens`, `sessions`, `cache`, `jobs`) pass without the key length error.

## If you still get the same error after that

It means your MariaDB version is old enough that even 191 characters at 4 bytes (764 bytes) plus other index overhead is tight, which is rare but happens on very old bundled MariaDB builds. In that case, open `config/database.php`, find the `mysql` connection array, and change:

```php
'charset' => 'utf8mb4',
'collation' => 'utf8mb4_unicode_ci',
```

to:

```php
'charset' => 'utf8',
'collation' => 'utf8_unicode_ci',
```

then run `php artisan migrate:fresh` again. This is a fallback though, the `defaultStringLength(191)` fix above is the standard one and should be enough in almost every case.

Once `migrate:fresh` runs clean, you're back on track: tell the AI editor to go ahead and generate the `DATABASE_PLAN.md` and `PROGRESS.md` files exactly as planned, review them, then say "approved" for the actual table migrations.