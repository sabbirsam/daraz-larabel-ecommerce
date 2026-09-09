# Progress Tracking

## GOAL
Build a high-concurrency, full-featured ecommerce web application in Laravel 12 styled after Daraz (Bangladesh's leading marketplace). The platform features isolated customer authentication and Filament admin panels, high-throughput database architecture with atomic anti-overselling inventory control, multi-tier caching, an interactive storefront (Blade + Tailwind + Alpine.js + Livewire), custom Cart service, Address & Checkout engine, SSLCommerz and Stripe payment gateways, mobile OTP phone verification, verified purchase reviews, order lifecycle management, coupons, and live search.

## PHASE STATUS
- [x] Phase 1: Project skeleton, Breeze customer auth, Filament admin panel installed and reachable at /admin with its own login (DONE)
- [x] Phase 2: Category and brand management in Filament, full product management in Filament including variants, attributes, stock, pricing, and image galleries (DONE)
- [x] Phase 3: Public storefront: home page, category listing pages with sidebar filters, single product page with gallery, specs, and reviews section (DONE)
- [x] Phase 4: Cart (guest and logged in) and wishlist (DONE)
- [x] Phase 5: Checkout: address form, delivery method choice, order summary, order creation (DONE)
- [ ] Phase 6: SSLCommerz payment integration, sandbox mode first, with success, fail, and cancel pages (NOT STARTED)
- [ ] Phase 7: Mobile OTP: send code, verify code, gate registration and checkout verification (NOT STARTED)
- [ ] Phase 8: Order management in Filament (status workflow, invoice view) and customer order history (NOT STARTED)
- [ ] Phase 9: Reviews and star ratings tied to verified purchases (NOT STARTED)
- [ ] Phase 10: Coupons, search bar with live results, and final responsive polish (NOT STARTED)

## CURRENT SESSION LOG
### 2026-09-09 - Session 1
- **Target**: Execute Phase 1, Phase 2, and Phase 3: Setup high-concurrency database, configure isolated Filament admin with separate `admin` guard, build catalog management resources, seed realistic marketplace products, and construct public storefront (Home, Category/Search listings with filters, and Single Product details with Alpine gallery).
- **Completed**:
  - Phase 1: Created 21 database migrations with composite indexes; built 20 Eloquent models; configured `admin` guard and `Admin` model implementing `FilamentUser`; seeded default admin and demo customer; styled Breeze customer authentication in Daraz orange (`#F85606`).
  - Phase 2: Built `CategoryResource`, `BrandResource`, `AttributeResource`, and `ProductResource` (with 5-tab design, image galleries, and dynamic variant repeaters); seeded realistic Daraz products via `ProductSeeder`.
  - Phase 3: Built the Public Storefront:
    - Master layout (`storefront.blade.php`) featuring Daraz orange header, central search bar, cart/wishlist counters, account dropdown, and category strip.
    - Home page (`home.blade.php`) with hero banner, category sidebar flyout, live countdown Flash Sale section, category icon grid, and responsive "Just For You" 4-column product feed.
    - Category & search listing page (`catalog/index.blade.php`) with left collapsible filter sidebar (category tree, brand checkboxes, price range, star rating) and sorting dropdown.
    - Single product page (`catalog/show.blade.php`) with interactive image gallery, live variant switcher dynamically recalculating price and stock, quantity stepper with max stock check, buy box, specifications table, and 5-star rating breakdown bar chart.
  - Automated Testing: Ran full test suite across auth, catalog, and storefront. All 35 tests passed cleanly (86 assertions).
- **Decisions Made**:
  - Storefront controllers utilize eager loading (`with(['images', 'brand', 'variants', 'reviews'])`) to eliminate N+1 queries.
  - Category filters are defensively structured to handle both root and nested subcategories with product count aggregates.
- **Problems & Solutions**:
  - SQLite in-memory testing on `ExampleTest`: Enabled `RefreshDatabase` trait.
  - Incomplete class issue during cache serialization: Cleared cache via `php artisan cache:clear` and added defensive type-checking in blade.

### 2026-09-09 - Session 2
- **Target**: Execute Phase 4 (Shopping Cart & Wishlist) and Phase 5 (Checkout, Addresses, Delivery Methods, and Atomic Concurrency-Safe Order Placement).
- **Completed**:
  - Phase 4:
    - Built `App\Services\CartService` managing guest session carts and database-persisted customer carts with automatic merge upon login.
    - Built `App\Listeners\MergeCartOnLogin` event listener.
    - Created `CartController` (index, add, update, remove, clear) and `WishlistController` (index, toggle, remove).
    - Crafted Daraz-styled Cart page (`resources/views/cart/index.blade.php`) with seller grouping, quantity steppers, subtotal, and order summary.
    - Built Wishlist page (`resources/views/wishlist/index.blade.php`).
    - Tested with `tests/Feature/CartTest.php` (8/8 passed).
  - Phase 5:
    - Configured standard Bangladesh administrative dataset (`config/bangladesh.php`) with all 8 divisions and their major districts.
    - Built high-concurrency `App\Services\OrderService`:
      - Uses `DB::transaction()` with row-level exclusive locks (`lockForUpdate()`) on all cart products and variants to prevent race conditions or overselling during flash sales.
      - Enforces strict inventory availability checks before creating order records.
      - Conditionally and atomically decrements product and variant stocks.
      - Generates authentic Daraz-style unique order numbers (`ORD-YYYYMMDD-XXXXXX`).
      - Records frozen financial snapshots of items, coupons, shipping, and addresses.
      - Empties cart and clears coupon session upon successful checkout.
    - Built `App\Http\Controllers\Storefront\CheckoutController` and `AddressController`:
      - Handles checkout view, address selection, inline address creation, delivery method switching (Standard vs Express), coupon application/removal, order creation, and order confirmation.
    - Created Daraz checkout templates:
      - `resources/views/checkout/index.blade.php`: 2-column layout, saved address radio list with "+ Add New Address" form, delivery options (Standard Free over ৳1500 vs Express), package review with thumbnails, Cash on Delivery (COD) and Online Payment (SSLCommerz) radio choices, promo voucher form with instant discount recalculation, and sticky order summary.
      - `resources/views/checkout/success.blade.php`: Order confirmation invoice page displaying order number, estimated delivery window, delivery address, payment method, itemized list, and price breakdown.
    - Seeded sample Daraz vouchers (`DARAZ10`, `SAVE100`, `DARAZ500`) and demo default customer address in `DatabaseSeeder.php`.
    - Created comprehensive automated test suite `tests/Feature/CheckoutTest.php` (10/10 tests passed).
    - Verified entire project test suite: **All 53 tests passed (149 assertions)**.
- **Decisions Made**:
  - Wrapped order placement in row-level database locks (`lockForUpdate()`) to guarantee zero overselling under high concurrency.
  - Implemented dynamic Alpine.js reactive calculations for shipping fees and totals on the checkout view to provide instant feedback without page reloads.
- **Problems & Solutions**:
  - Eloquent model property collision: `$this->attributes` in `ProductVariant` collided with Eloquent's internal attributes array, solved by accessing the JSON column directly via cast parameter `get: fn ($value, array $attributes) => ...`.

## WHAT IS LEFT (Phase 6)
- SSLCommerz Payment Gateway Integration:
  - Create `PaymentGatewayInterface` contract so gateways (SSLCommerz, Stripe) can be swapped or added without changing checkout code.
  - Create `SSLCommerzService` with sandbox mode credentials, transaction initialization, redirect URLs, and IPN / callback verification.
  - Implement payment callback routes (`/payment/sslcommerz/success`, `/payment/sslcommerz/fail`, `/payment/sslcommerz/cancel`, `/payment/sslcommerz/ipn`).
  - Update `orders` and `payments` tables with transaction ID, card/account type, and validation status upon callback.
  - Dedicated Payment Status view pages (Success, Failure, Cancellation).

## NEXT STEP
Create `PaymentGatewayInterface` and implement `SSLCommerzService` with sandbox mode, callback routes, and payment status views for Phase 6.
