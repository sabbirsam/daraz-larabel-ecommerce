# Laravel Ecommerce Build Guide (Daraz style store)

This is your single reference document. Keep it open in one tab while you work in the AI editor. Part 10 contains the master prompt you paste into the AI editor to start the actual build.

From 1 to 9 done with database creations 

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