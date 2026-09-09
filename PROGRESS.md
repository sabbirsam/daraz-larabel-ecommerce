# Progress Tracking

## GOAL
Build a high-concurrency, full-featured ecommerce web application in Laravel 12 styled after Daraz (Bangladesh's leading marketplace). The platform features isolated customer authentication and Filament admin panels, high-throughput database architecture with atomic anti-overselling inventory control, multi-tier caching, an interactive storefront (Blade + Tailwind + Alpine.js + Livewire), custom Cart service, Address & Checkout engine, SSLCommerz and Stripe payment gateways, mobile OTP phone verification, verified purchase reviews, order lifecycle management, coupons, and live search.

## PHASE STATUS
- [x] Phase 1: Project skeleton, Breeze customer auth, Filament admin panel installed and reachable at /admin with its own login (DONE)
- [x] Phase 2: Category and brand management in Filament, full product management in Filament including variants, attributes, stock, pricing, and image galleries (DONE)
- [x] Phase 3: Public storefront: home page, category listing pages with sidebar filters, single product page with gallery, specs, and reviews section (DONE)
- [ ] Phase 4: Cart (guest and logged in) and wishlist (IN PROGRESS)
- [ ] Phase 5: Checkout: address form, delivery method choice, order summary, order creation (NOT STARTED)
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

## WHAT IS LEFT (Phase 4)
- Shopping Cart & Wishlist Implementation:
  - Custom `App\Services\CartService` managing guest session carts and database-persisted customer carts with automatic merge upon login.
  - Variant-aware cart item handling (differentiating by `product_id` + `variant_id` with price override support).
  - Stock validation preventing adding more items than available inventory.
  - Full Cart page with seller/shop groupings, item quantity steppers, item deletion, subtotal calculations, and order summary sidebar.
  - Wishlist toggle with instant badge counter updates.

## NEXT STEP
Create `App\Services\CartService` and wire up the cart controller, cart routes, and cart page view at `/cart`.
