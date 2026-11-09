# ShopDeck — Complete Phase-by-Phase Development Plan
## Small E-commerce Management System | Candidate Assignment

**Project:** ShopDeck  
**Repository:** `shopdeck`  
**Project description:** A multi-shop e-commerce management system with a shared product catalog.  
**Required stack:** Laravel (PHP) + Vue.js or React.js + MySQL/compatible relational database  
**Recommended stack:** Laravel + Vue 3 + Inertia.js + MySQL + Tailwind CSS  
**Assignment deadline:** 11 November 2026  
**Plan prepared:** 9 November 2026

---

# 0. How to Use This Document

এই document-টি ShopDeck assignment-এর source requirement এবং আগের requirement analysis ধরে implementation checklist হিসেবে তৈরি করা হয়েছে। কাজ করার সময় phase ধরে এগোবে। প্রতিটি phase-এর:

- Goal — phase শেষে কী complete হবে।
- Tasks — কোন কাজগুলো করতে হবে।
- Suggested files — কোন জায়গায় code থাকার সম্ভাবনা।
- Acceptance criteria — কীভাবে বুঝবে কাজটি ঠিক হয়েছে।
- Suggested commit — কাজ শেষ হলে কীভাবে commit করবে।

**গুরুত্বপূর্ণ:** Suggested file path project-এর Laravel version, starter kit এবং existing structure অনুযায়ী সামান্য বদলাতে পারে। অপ্রয়োজনীয় folder structure জোর করে তৈরি করবে না।

## Core business rule — পুরো project-এর ভিত্তি

একটি product একবার shared catalog-এ থাকবে। একই product একাধিক shop-এ যুক্ত হতে পারবে। কিন্তু প্রতিটি shop তার listing-এর price, description/details, thumbnail এবং active status আলাদাভাবে নিয়ন্ত্রণ করবে।

- `products` = shared/global product information.
- `shop_products` = shop-specific listing and overrides.
- Customer শুধু selected shop-এ assigned এবং active product দেখবে।
- Catalog-এ product থাকা মানেই customer-facing shop-এ product visible হওয়া নয়।

---

# 1. Requirement Summary

## 1.1 User roles

### Admin
- Login/logout.
- Shop CRUD.
- Shop owner account create/manage.
- Global product CRUD.
- Shared catalog-এর সব product ও creator দেখতে পারা।
- Shop এবং product active/inactive করা।

### Shop Owner
- Login/logout.
- নিজের shop dashboard দেখা।
- Shared catalog browse/search করা।
- অন্য owner বা admin-এর তৈরি active product নিজের shop-এ add করা।
- নিজের product তৈরি করা; সেটি shared catalog-এ যোগ হবে এবং নিজের shop-এও listing হবে।
- নিজের shop listing-এর price, details/description, thumbnail update করা।
- নিজের shop থেকে product remove/unassign করা।
- অন্য shop-এর listing বা shop information পরিবর্তন করতে না পারা।

### Customer / Public Visitor
- Public shop page দেখতে পারা।
- শুধু ওই shop-এ assigned ও active product দেখা।
- Shop-specific name/details, price ও thumbnail দেখা।
- Unassigned product, অন্য shop-এর data এবং dashboard দেখতে না পারা।

## 1.2 Explicitly out of scope
Assignment-এ নিচের feature চাওয়া হয়নি। Core requirements শেষ না করে এগুলোতে সময় দেবে না:

- Cart / checkout
- Order management
- Payment gateway
- Inventory / stock management
- Delivery management
- Coupons
- Advanced analytics
- Customer account system

## 1.3 Recommended scope decisions

সময়ের মধ্যে কাজ শেষ করতে নিচের simple rules নেওয়া হয়েছে:

1. একজন shop-এর একজন primary owner থাকবে (`shops.owner_id`)।
2. Admin ও Shop Owner একই `users` table ব্যবহার করবেন; role দিয়ে access আলাদা হবে।
3. Owner-created product shared catalog-এ active অবস্থায় আসবে, যদি creation সফল হয়।
4. Owner অন্যের global product edit করবেন না; নিজের shop listing override করবেন।
5. Product create এবং creator-এর shop listing create একই database transaction-এ হবে।
6. Product একই shop-এ একবারই add করা যাবে — database unique constraint দিয়ে নিশ্চিত করতে হবে।
7. Product image Laravel public storage disk-এ থাকবে; database-এ path সংরক্ষণ হবে।
8. Shop/customer public page-এ shop inactive হলে product দেখানো হবে না।

যদি assignment evaluator অন্য interpretation চান, README-তে নিজের implemented rule স্পষ্ট করে লিখবে।

---

# 2. Data Model & Database Design

## 2.1 Tables

### `users`
প্রস্তাবিত columns:

| Column | Type / Rule |
|---|---|
| id | Primary key |
| name | string |
| email | unique |
| password | hashed |
| role | `admin` / `shop_owner` |
| is_active | boolean, default true |
| timestamps | created_at, updated_at |

Existing Laravel users migration থাকলে সেটি extend করো; duplicate users table বানাবে না।

### `shops`

| Column | Type / Rule |
|---|---|
| id | Primary key |
| name | string |
| slug | unique |
| description | nullable text |
| logo | nullable string |
| owner_id | foreign key → users.id |
| is_active | boolean, default true |
| timestamps | created_at, updated_at |

একজন owner একটি shop পরিচালনা করবেন—এই assignment-এর simple implementation। Multiple-shop ownership এখন implement করা বাধ্যতামূলক নয়।

### `products`

| Column | Type / Rule |
|---|---|
| id | Primary key |
| name | string |
| sku | nullable; supplied হলে unique |
| description | nullable text |
| thumbnail | nullable string |
| created_by | foreign key → users.id |
| is_active | boolean, default true |
| timestamps | created_at, updated_at |

`created_by` দিয়ে বোঝা যাবে product admin নাকি কোন shop owner তৈরি করেছেন।

### `shop_products`

| Column | Type / Rule |
|---|---|
| id | Primary key |
| shop_id | foreign key → shops.id |
| product_id | foreign key → products.id |
| price | decimal(10,2), non-negative |
| name_override | nullable string |
| description_override | nullable text |
| thumbnail_override | nullable string |
| is_active | boolean, default true |
| timestamps | created_at, updated_at |

Constraints:
- `UNIQUE(shop_id, product_id)`
- `shop_id` foreign key.
- `product_id` foreign key.
- Price validation: `numeric`, `min:0`.
- Foreign key delete behavior consciously select করতে হবে।

## 2.2 Relationships

```text
User
 ├── hasOne Shop (owner)
 └── hasMany Products (created products)

Shop
 ├── belongsTo User (owner)
 ├── hasMany ShopProducts
 └── belongsToMany Products through shop_products

Product
 ├── belongsTo User (creator)
 ├── hasMany ShopProducts
 └── belongsToMany Shops through shop_products

ShopProduct
 ├── belongsTo Shop
 └── belongsTo Product
```

`ShopProduct`-কে আলাদা Eloquent model হিসেবে রাখলে listing validation, update এবং authorization সহজ হবে।

## 2.3 Shop-specific display resolution

Customer page-এ:

- Product name: `name_override ?? product.name`
- Description: `description_override ?? product.description`
- Thumbnail: `thumbnail_override ?? product.thumbnail`
- Price: `shop_products.price`
- Visibility: shop active AND listing active AND product active.

**কখনোই customer-facing product price global product থেকে নেবে না।** Price shop listing-এর field।

---

# 3. Phase 0 — Project Preparation & Repository Setup

## Goal
Clean Laravel project, Git repository এবং local environment প্রস্তুত করা।

## Step-by-step tasks

1. Project root directory নিশ্চিত করো।
2. Laravel version ও PHP requirement যাচাই করো।
3. Git repository initialize/verify করো।
4. `.gitignore`-এ `.env`, vendor, node_modules এবং generated files আছে কি না দেখো।
5. `.env.example` থাকবে; real `.env` commit করবে না।
6. MySQL database তৈরি করো।
7. Composer এবং Node dependencies install করো।
8. Existing app চালিয়ে starter setup verify করো।
9. README-এর initial project description যোগ করো।

## Suggested commands

```bash
composer install
cp .env.example .env
php artisan key:generate

# .env file-এ DB_DATABASE, DB_USERNAME, DB_PASSWORD configure করো

php artisan migrate
npm install
npm run build
php artisan serve
```

Development frontend server:

```bash
npm run dev
```

## Acceptance criteria

- Laravel application boot হয়।
- Database connection কাজ করে।
- Migrations চলে।
- Frontend build সফল হয়।
- `.env` Git status-এ staged/untracked হিসেবে আসে না।
- Fresh setup-এর জন্য প্রয়োজনীয় configuration example আছে।

## Suggested commit

```text
chore: initialize ShopDeck project

Description:
Set up the Laravel application and prepare the initial project environment.

Changes:
- Initialized the application structure.
- Added environment configuration example.
- Prepared frontend and backend dependencies.

Files Changed:
- .env.example
- composer.json
- package.json
- README.md
```

---

# 4. Phase 1 — Authentication & Role-based Access

## Goal
Admin ও Shop Owner login করতে পারবেন এবং role অনুযায়ী protected area access করতে পারবেন।

## Step-by-step tasks

1. Laravel authentication scaffolding/setup complete করো।
2. Existing `users` table-এ `role` column যোগ করো।
3. `is_active` column যোগ করো, যদি account status implement করো।
4. User model-এ role constants/enum বা consistent role values define করো।
5. Admin middleware তৈরি করো।
6. Shop Owner middleware তৈরি করো।
7. Admin routes protected করো।
8. Owner routes protected করো।
9. Inactive account login/access block করো।
10. Login-এর পর role অনুযায়ী dashboard redirect করো।
11. Logout কাজ করছে কি না verify করো।

## Suggested files

```text
app/Models/User.php
app/Http/Middleware/EnsureUserIsAdmin.php
app/Http/Middleware/EnsureUserIsShopOwner.php
bootstrap/app.php                 # Laravel version অনুযায়ী middleware registration
routes/web.php
database/migrations/*_add_role_to_users_table.php
resources/js/Pages/Auth/*
```

## Route outline

```text
/login
/logout

/admin/dashboard
/owner/dashboard
```

Route names উদাহরণ:
- `admin.dashboard`
- `owner.dashboard`

## Authorization rules

- Guest protected page access করতে পারবেন না।
- Shop Owner admin route access করতে পারবেন না।
- Admin owner-only route access করতে পারবেন না, যদি route policy সেটি restrict করে।
- Role check শুধু frontend menu hide করে করা যাবে না; backend middleware/policy লাগবে।

## Acceptance criteria

- Admin login করে admin dashboard দেখেন।
- Shop Owner login করে owner dashboard দেখেন।
- Guest dashboard access করলে login page-এ redirect হন।
- Wrong role access করলে 403/redirect হয়।
- Inactive account protected action করতে পারে না।

## Suggested commit

```text
feat(auth): implement role-based authentication

Description:
Added authentication and separated admin and shop owner access.

Changes:
- Added user role and account status fields.
- Implemented admin and shop owner middleware.
- Protected role-specific dashboard routes.
- Added role-based post-login redirection.

Files Changed:
- app/Models/User.php
- app/Http/Middleware/EnsureUserIsAdmin.php
- app/Http/Middleware/EnsureUserIsShopOwner.php
- routes/web.php
- database/migrations/*_add_role_to_users_table.php
```

---

# 5. Phase 2 — Database Migrations, Models & Relationships

## Goal
Shared catalog এবং shop-specific listing-এর correct relational foundation তৈরি করা।

## Step-by-step tasks

1. `shops` migration তৈরি করো।
2. `products` migration তৈরি করো।
3. `shop_products` migration তৈরি করো।
4. Foreign key এবং delete behavior configure করো।
5. `(shop_id, product_id)` unique constraint যোগ করো।
6. Price `decimal(10,2)` রাখো।
7. `Shop` model তৈরি করো।
8. `Product` model তৈরি করো।
9. `ShopProduct` model তৈরি করো।
10. `User`, `Shop`, `Product`, `ShopProduct` relationships define করো।
11. `$fillable`/`$guarded` নিরাপদভাবে configure করো।
12. Migration fresh database-এ run করো।
13. Tinker বা ছোট test দিয়ে relationships verify করো।

## Suggested files

```text
app/Models/User.php
app/Models/Shop.php
app/Models/Product.php
app/Models/ShopProduct.php
database/migrations/*_create_shops_table.php
database/migrations/*_create_products_table.php
database/migrations/*_create_shop_products_table.php
```

## Important implementation notes

- `created_by` authenticated user থেকে set করবে; request payload থেকে blindly নেবে না।
- Owner-এর `shop_id` authenticated user-এর relation থেকে resolve করবে।
- Client পাঠানো arbitrary `shop_id` trust করবে না।
- Product listing duplicate prevent করার primary guarantee database unique constraint।
- Product ও listing আলাদা entity হিসেবে treat করবে।

## Acceptance criteria

- সব migration fresh database-এ pass করে।
- User shop owner relationship কাজ করে।
- Product creator relationship কাজ করে।
- Shop ও Product-এর many-to-many relationship pivot data-সহ কাজ করে।
- একই shop/product pair duplicate insert করলে database reject করে।

## Suggested commit

```text
feat(database): add shop catalog and listing schema

Description:
Created the relational database structure for shops, shared products and shop-specific listings.

Changes:
- Added shops and products tables.
- Added shop_products listing table.
- Added foreign keys and unique shop-product constraint.
- Implemented Eloquent model relationships.

Files Changed:
- app/Models/User.php
- app/Models/Shop.php
- app/Models/Product.php
- app/Models/ShopProduct.php
- database/migrations/*_create_shops_table.php
- database/migrations/*_create_products_table.php
- database/migrations/*_create_shop_products_table.php
```

---

# 6. Phase 3 — Admin Panel: Shop Management

## Goal
Admin shop create, view, update, status change ও delete করতে পারবেন।

## Step-by-step tasks

1. Admin layout তৈরি করো।
2. Sidebar-এ Dashboard, Shops, Shop Owners, Products navigation রাখো।
3. Shop list page তৈরি করো।
4. Shop create form তৈরি করো।
5. Shop edit form তৈরি করো।
6. Shop details view দরকার হলে তৈরি করো।
7. Owner dropdown-এ শুধু active `shop_owner` account দেখাও।
8. Shop name required validation।
9. Slug generate/validate করো এবং unique রাখো।
10. Description/logo optional রাখো।
11. Active/inactive status toggle implement করো।
12. Delete confirmation implement করো।
13. Shop delete হলে related listing কী হবে, migration foreign key rule-এর সঙ্গে মিলিয়ে verify করো।
14. Unauthorized user যেন admin shop action করতে না পারে তা test করো।

## Suggested backend files

```text
app/Http/Controllers/Admin/ShopController.php
app/Http/Requests/Admin/StoreShopRequest.php
app/Http/Requests/Admin/UpdateShopRequest.php
app/Models/Shop.php
routes/web.php
```

## Suggested frontend files

```text
resources/js/Layouts/AdminLayout.vue
resources/js/Pages/Admin/Shops/Index.vue
resources/js/Pages/Admin/Shops/Create.vue
resources/js/Pages/Admin/Shops/Edit.vue
resources/js/Components/ShopForm.vue
```

## Shop fields

- Name
- Slug
- Owner
- Description
- Logo (optional)
- Active status

Contact email/phone/address optional enhancement; time short হলে বাদ দেওয়া যাবে।

## Acceptance criteria

- Admin shop list দেখতে পারেন।
- Admin নতুন shop create করতে পারেন।
- Existing shop update করতে পারেন।
- Slug duplicate হলে validation error আসে।
- Valid owner assign করা যায়।
- Inactive shop public customer view-এ accessible নয়।
- Owner shop management route access করতে পারেন না।

## Suggested commit

```text
feat(admin): implement shop management

Description:
Added admin functionality to manage shops and assign shop owners.

Changes:
- Implemented shop listing, creation and editing.
- Added owner assignment and slug validation.
- Added active/inactive status management.
- Added shop deletion confirmation.

Files Changed:
- app/Http/Controllers/Admin/ShopController.php
- app/Http/Requests/Admin/StoreShopRequest.php
- app/Http/Requests/Admin/UpdateShopRequest.php
- resources/js/Pages/Admin/Shops/Index.vue
- resources/js/Pages/Admin/Shops/Create.vue
- resources/js/Pages/Admin/Shops/Edit.vue
- routes/web.php
```

---

# 7. Phase 4 — Admin Panel: Shop Owner Management

## Goal
Admin shop owner account create এবং manage করতে পারবেন।

## Step-by-step tasks

1. Shop Owner list page তৈরি করো।
2. Create owner form তৈরি করো।
3. Name, email, password validate করো।
4. Email unique রাখো।
5. Password hash করে save করো।
6. Role server-side-এ `shop_owner` set করো।
7. Account active/inactive action যোগ করো।
8. Owner edit/update implement করো।
9. Owner-এর assigned shop দেখাও।
10. Owner-কে shop assign করার flow implement করো।
11. Owner delete করলে assigned shop-এর কী হবে তা ঠিক করো; assignment না থাকলে delete/disable safely করো।
12. Admin ছাড়া অন্য কেউ owner account create করতে না পারে তা verify করো।

## Suggested files

```text
app/Http/Controllers/Admin/ShopOwnerController.php
app/Http/Requests/Admin/StoreShopOwnerRequest.php
app/Http/Requests/Admin/UpdateShopOwnerRequest.php
resources/js/Pages/Admin/ShopOwners/Index.vue
resources/js/Pages/Admin/ShopOwners/Create.vue
resources/js/Pages/Admin/ShopOwners/Edit.vue
```

## Acceptance criteria

- Admin owner create করতে পারেন।
- Email duplicate হলে reject হয়।
- Owner role client থেকে পরিবর্তন করা যায় না।
- Owner account inactive করা যায়।
- Owner assigned shop দেখতে পারেন।
- Owner অন্য owner account manage করতে পারেন না।

## Suggested commit

```text
feat(admin): add shop owner account management

Description:
Implemented admin controls for creating and managing shop owner accounts.

Changes:
- Added shop owner listing and creation.
- Added owner profile update and account status controls.
- Added shop assignment.
- Enforced server-side shop owner role assignment.

Files Changed:
- app/Http/Controllers/Admin/ShopOwnerController.php
- app/Http/Requests/Admin/StoreShopOwnerRequest.php
- app/Http/Requests/Admin/UpdateShopOwnerRequest.php
- resources/js/Pages/Admin/ShopOwners/Index.vue
- resources/js/Pages/Admin/ShopOwners/Create.vue
- resources/js/Pages/Admin/ShopOwners/Edit.vue
- routes/web.php
```

---

# 8. Phase 5 — Admin Panel: Shared Product Management

## Goal
Admin global/shared product create, view, update ও manage করতে পারবেন।

## Step-by-step tasks

1. Admin product list page তৈরি করো।
2. Product create form তৈরি করো।
3. Product edit form তৈরি করো।
4. Product name required validation।
5. SKU optional রাখলে supplied value unique validate করো।
6. Description optional রাখো।
7. Thumbnail upload implement করো।
8. Upload validation: image MIME/type ও reasonable max size।
9. Image public disk-এ save করো।
10. `created_by` authenticated admin ID থেকে set করো।
11. `is_active` default true রাখো।
12. Product creator/author list-এ দেখাও।
13. Product active/inactive control যোগ করো।
14. Delete action-এর আগে product assigned আছে কি না বিবেচনা করো।
15. Existing product thumbnail replace করলে পুরোনো file safely handle করো।

## Suggested files

```text
app/Http/Controllers/Admin/AdminProductController.php
app/Http/Requests/Admin/StoreProductRequest.php
app/Http/Requests/Admin/UpdateProductRequest.php
app/Models/Product.php
resources/js/Pages/Admin/Products/Index.vue
resources/js/Pages/Admin/Products/Create.vue
resources/js/Pages/Admin/Products/Edit.vue
resources/js/Components/ProductForm.vue
```

## Acceptance criteria

- Admin global product create করতে পারেন।
- Product shared catalog data হিসেবে save হয়।
- Creator ID সঠিক admin user হয়।
- Product image URL/path ঠিকভাবে resolve হয়।
- Admin product edit ও inactive করতে পারেন।
- Product list-এ creator দেখা যায়।

## Suggested commit

```text
feat(admin): implement shared product management

Description:
Added admin product management for the centralized product catalog.

Changes:
- Implemented product listing, creation and editing.
- Added product thumbnail upload and validation.
- Added creator tracking.
- Added product active/inactive controls.

Files Changed:
- app/Http/Controllers/Admin/AdminProductController.php
- app/Http/Requests/Admin/StoreProductRequest.php
- app/Http/Requests/Admin/UpdateProductRequest.php
- resources/js/Pages/Admin/Products/Index.vue
- resources/js/Pages/Admin/Products/Create.vue
- resources/js/Pages/Admin/Products/Edit.vue
- resources/js/Components/ProductForm.vue
- routes/web.php
```

---

# 9. Phase 6 — Shop Owner Dashboard & Shared Catalog

## Goal
Shop Owner shared catalog browse/search করে নিজের shop-এ product add করতে পারবেন।

## Step-by-step tasks

1. Owner layout তৈরি করো।
2. Owner dashboard তৈরি করো।
3. Authenticated owner-এর shop backend থেকে resolve করো।
4. Owner-এর shop না থাকলে clear empty/error state দেখাও।
5. Shared catalog page তৈরি করো।
6. Active global products fetch করো।
7. Product thumbnail, name, description preview দেখাও।
8. Creator name/role দেখাও।
9. Search implement করো।
10. Pagination implement করো।
11. Current owner's shop-এ product already added কি না query করো।
12. Not added হলে `Add to My Shop` button দেখাও।
13. Already added হলে `Added` state দেখাও।
14. Add action backend-এ ownership check করবে।
15. Duplicate add হলে friendly validation response দাও।
16. Database unique constraint duplicate race condition আটকাবে।

## Suggested files

```text
app/Http/Controllers/Owner/CatalogController.php
app/Http/Controllers/Owner/ShopProductController.php
app/Http/Requests/Owner/AddProductToShopRequest.php
resources/js/Layouts/OwnerLayout.vue
resources/js/Pages/Owner/Dashboard.vue
resources/js/Pages/Owner/Catalog/Index.vue
resources/js/Components/ProductCard.vue
routes/web.php
```

## Add-to-shop backend logic

1. Authenticated user।
2. User-এর assigned shop resolve।
3. Requested product exists এবং active কি না verify।
4. `(shop_id, product_id)` আগে থেকেই আছে কি না check।
5. Shop listing create।
6. Initial price-এর জন্য একটি explicit rule রাখো: form/modal থেকে owner price দেবে, অথবা documented default value ব্যবহার করবে। Price zero বা null silently set করবে না।
7. Success response ও updated UI।

**Recommendation:** Add action-এ ছোট modal/form খুলে shop owner-কে price দিতে বলো। এতে invalid বা missing price নিয়ে ambiguity থাকবে না।

## Acceptance criteria

- Owner shared catalog-এ admin ও অন্য owner-created product দেখতে পারেন।
- Inactive product add করতে পারেন না।
- Owner নিজের shop-এ product add করতে পারেন।
- একই product দ্বিতীয়বার add করলে duplicate তৈরি হয় না।
- অন্য owner-এর shop ID পাঠিয়ে add করা যায় না।

## Suggested commit

```text
feat(owner): add shared product catalog

Description:
Implemented the shared catalog browsing and product assignment workflow for shop owners.

Changes:
- Added owner dashboard and catalog listing.
- Added product search and pagination.
- Displayed product creator and assignment state.
- Implemented add-to-own-shop action.
- Prevented duplicate shop product assignments.

Files Changed:
- app/Http/Controllers/Owner/CatalogController.php
- app/Http/Controllers/Owner/ShopProductController.php
- app/Http/Requests/Owner/AddProductToShopRequest.php
- resources/js/Pages/Owner/Dashboard.vue
- resources/js/Pages/Owner/Catalog/Index.vue
- resources/js/Components/ProductCard.vue
- routes/web.php
```

---

# 10. Phase 7 — Shop Owner: Create Own Product & Manage Listings

## Goal
Owner নিজের product create করবেন; product shared catalog-এ থাকবে এবং নিজের shop-এ listing হবে। Owner নিজের listing-এর price/details/thumbnail edit করতে পারবেন।

## 10.1 Create own product

### Step-by-step tasks

1. Owner product create page তৈরি করো।
2. Name, description, thumbnail fields রাখো।
3. Shop-specific initial price collect করো।
4. Product input validate করো।
5. `created_by` authenticated owner ID থেকে set করো।
6. Owner-এর shop resolve করো; request-এর shop ID trust করো না।
7. Database transaction শুরু করো।
8. Global `products` row create করো।
9. `shop_products` row create করো।
10. Transaction commit করো।
11. কোনো step fail হলে rollback করো।
12. Success-এর পর owner listing page-এ redirect করো।

## 10.2 Manage own shop listings

### Step-by-step tasks

1. Owner-এর assigned listing list দেখাও।
2. Listing-এর global product data ও shop override একসঙ্গে দেখাও।
3. Edit form-এ price রাখো।
4. Name/details override field রাখো।
5. Thumbnail override upload রাখো।
6. Listing active/inactive control রাখো।
7. Update-এর আগে listing owner-এর shop-এর কি না verify করো।
8. Remove/unassign action যোগ করো।
9. Delete/remove confirmation দেখাও।
10. অন্য shop-এর listing ID দিয়ে request করলে 403/404 return করো।

## Suggested files

```text
app/Http/Controllers/Owner/OwnerProductController.php
app/Http/Controllers/Owner/ShopProductController.php
app/Http/Requests/Owner/StoreOwnerProductRequest.php
app/Http/Requests/Owner/UpdateShopProductRequest.php
resources/js/Pages/Owner/Products/Index.vue
resources/js/Pages/Owner/Products/Create.vue
resources/js/Pages/Owner/Products/Edit.vue
resources/js/Components/ProductForm.vue
```

## Ownership rule

Shop Owner global catalog product-এর base information edit করবেন না, যদি সেটি অন্য user তৈরি করে থাকেন। Owner নিজের shop listing-এর override fields পরিবর্তন করবেন।

নিজের তৈরি global product-এর base data edit permission দিলে creator check করতে হবে:

```text
product.created_by == authenticated_user.id
```

তবে deadline-এর জন্য base product edit এবং listing edit আলাদা action রাখাই সহজ ও পরিষ্কার।

## Acceptance criteria

- Owner-created product shared catalog-এ দেখা যায়।
- Owner-created product creator-এর shop-এ সঙ্গে সঙ্গে assigned হয়।
- Transaction failure হলে incomplete product/listing পড়ে থাকে না।
- Owner নিজের listing-এর price update করতে পারেন।
- Owner নিজের listing-এর details ও thumbnail update করতে পারেন।
- Shop A update করলে Shop B-এর listing অপরিবর্তিত থাকে।
- Owner অন্য shop-এর listing update/remove করতে পারেন না।

## Suggested commit

```text
feat(owner): implement product creation and listing management

Description:
Allowed shop owners to create shared products and manage their own shop listings.

Changes:
- Added owner product creation with transactional listing assignment.
- Added shop-specific price and details updates.
- Added thumbnail override upload.
- Added listing active/inactive and remove actions.
- Enforced listing ownership checks.

Files Changed:
- app/Http/Controllers/Owner/OwnerProductController.php
- app/Http/Controllers/Owner/ShopProductController.php
- app/Http/Requests/Owner/StoreOwnerProductRequest.php
- app/Http/Requests/Owner/UpdateShopProductRequest.php
- resources/js/Pages/Owner/Products/Index.vue
- resources/js/Pages/Owner/Products/Create.vue
- resources/js/Pages/Owner/Products/Edit.vue
- resources/js/Components/ProductForm.vue
- routes/web.php
```

---

# 11. Phase 8 — Public Customer Shop View

## Goal
Customer নির্দিষ্ট shop-এর page-এ শুধু ওই shop-এ assigned active products দেখবেন।

## Step-by-step tasks

1. Public shop route তৈরি করো: `/shops/{slug}`।
2. Slug দিয়ে shop resolve করো।
3. Shop active কি না verify করো।
4. Shop-এর `shop_products` relation দিয়ে listing fetch করো।
5. Listing active filter করো।
6. Global product active filter করো।
7. Product name override fallback implement করো।
8. Description override fallback implement করো।
9. Thumbnail override fallback implement করো।
10. Price pivot/listing থেকে নাও।
11. Responsive product grid তৈরি করো।
12. Shop-এ product না থাকলে empty state দেখাও।
13. Inactive/unknown shop-এর জন্য 404 বা appropriate unavailable page দেখাও।
14. অন্য shop-এর listing/customer data response-এ accidentally expose হচ্ছে কি না verify করো।

## Suggested files

```text
app/Http/Controllers/Public/PublicShopController.php
resources/js/Pages/Public/Shop.vue
resources/js/Components/PublicProductCard.vue
routes/web.php
```

## Required query logic

Conceptually:

```text
Find active shop by slug
  -> load only its shop_products
  -> where listing is active
  -> where related global product is active
  -> render resolved shop-specific fields
```

`Product::all()` করে customer page-এ সব product পাঠানো যাবে না।

## Acceptance criteria

- Shop A page-এ শুধু Shop A-এর assigned products দেখা যায়।
- Shop B page-এ শুধু Shop B-এর assigned products দেখা যায়।
- Unassigned product কোনো shop page-এ দেখা যায় না।
- Shop-specific price সঠিক।
- Shop-specific thumbnail সঠিক।
- Shop-specific description সঠিক।
- Inactive listing hide হয়।
- Inactive global product hide হয়।
- Inactive shop public page-এ product দেখায় না।

## Suggested commit

```text
feat(storefront): add public shop product listing

Description:
Implemented public shop pages that display only products assigned to each active shop.

Changes:
- Added public shop route and controller.
- Filtered products by shop assignment and active status.
- Added shop-specific field fallback resolution.
- Added responsive customer-facing product grid.

Files Changed:
- app/Http/Controllers/Public/PublicShopController.php
- resources/js/Pages/Public/Shop.vue
- resources/js/Components/PublicProductCard.vue
- routes/web.php
```

---

# 12. Phase 9 — Validation, Authorization & Feature Testing

## Goal
Business rules, data isolation এবং customer visibility test দিয়ে verify করা।

## 12.1 Authentication & authorization tests

- Guest admin page access করতে পারে না।
- Guest owner page access করতে পারে না।
- Shop Owner admin-only action করতে পারে না।
- Admin-only route guest/owner থেকে protected।
- Inactive user protected action করতে পারে না।
- Owner অন্য owner-এর listing update করতে পারে না।
- Owner অন্য owner-এর listing delete/remove করতে পারে না।

## 12.2 Shared catalog tests

- Admin-created product catalog-এ থাকে।
- Owner-created product catalog-এ থাকে।
- Owner A-created product Owner B catalog-এ দেখা যায়।
- Inactive product add করা যায় না।
- Same product একই shop-এ duplicate হয় না।
- Same product different shops-এ add করা যায়।

## 12.3 Shop listing tests

- Same product-এর Shop A ও Shop B price আলাদা হতে পারে।
- Thumbnail override আলাদা হতে পারে।
- Description override আলাদা হতে পারে।
- Shop A update করলে Shop B-এর data পরিবর্তন হয় না।
- Owner নিজের shop-এর listing-ই update করতে পারেন।
- Invalid/negative price reject হয়।
- Invalid image type/oversized image reject হয়।

## 12.4 Customer visibility tests

- Customer only assigned product দেখে।
- Unassigned product hidden।
- Listing inactive হলে hidden।
- Global product inactive হলে hidden।
- Shop inactive হলে public listing বন্ধ।
- Customer অন্য shop-এর price/image দেখতে পায় না।

## Suggested test files

```text
tests/Feature/Auth/RoleAccessTest.php
tests/Feature/Admin/ShopManagementTest.php
tests/Feature/Admin/ShopOwnerManagementTest.php
tests/Feature/Admin/ProductManagementTest.php
tests/Feature/Owner/SharedCatalogTest.php
tests/Feature/Owner/ShopProductManagementTest.php
tests/Feature/Public/PublicShopTest.php
```

Test file name ও folder existing Laravel test convention অনুযায়ী adjust করো।

## Acceptance criteria

- Core business rules-এর automated tests pass করে।
- Cross-shop data leakage test করা হয়েছে।
- Duplicate assignment database level-এ blocked।
- Manual browser test-এ Admin → Owner → Customer workflow complete হয়।

## Suggested commit

```text
test: cover shop catalog ownership and storefront rules

Description:
Added feature tests for role access, shared catalog, shop listing isolation and customer visibility.

Changes:
- Added admin and owner authorization tests.
- Added shared catalog and duplicate assignment tests.
- Added shop-specific listing isolation tests.
- Added public shop visibility tests.

Files Changed:
- tests/Feature/Auth/RoleAccessTest.php
- tests/Feature/Admin/ShopManagementTest.php
- tests/Feature/Owner/SharedCatalogTest.php
- tests/Feature/Owner/ShopProductManagementTest.php
- tests/Feature/Public/PublicShopTest.php
```

---

# 13. Phase 10 — Seeders & Demo Data

## Goal
Reviewer যেন project run করেই demo account দিয়ে login করে core features test করতে পারেন।

## Suggested seed data

### Admin
- Name: `ShopDeck Admin`
- Email: `admin@example.com`
- Password: development-only demo password
- Role: `admin`

### Shop Owners
- `owner1@example.com`
- `owner2@example.com`
- Both role: `shop_owner`

### Shops
- Fashion House → Owner 1
- Trendy Store → Owner 2

### Products
- Cotton T-Shirt — Admin-created
- Casual Shirt — Owner 1-created
- Sneakers — Owner 2-created

### Listings
- Cotton T-Shirt → Fashion House, price ৳650
- Cotton T-Shirt → Trendy Store, price ৳720
- Casual Shirt → Fashion House
- Sneakers → Trendy Store

কমপক্ষে একটি global product কোনো shop-এ assign না করে রাখবে, যাতে customer view-তে সেটি hidden থাকে—এটি demo করা যায়।

## Step-by-step tasks

1. `DatabaseSeeder` তৈরি/update করো।
2. Admin account create করো।
3. দুইটি shop owner create করো।
4. দুইটি shop create ও owner assign করো।
5. Shared catalog product create করো।
6. Shop listing attach করো।
7. একই product দুই shop-এ আলাদা price-এ রাখো।
8. Unassigned product রাখো।
9. Seeder idempotent রাখার জন্য `updateOrCreate`/`firstOrCreate` বিবেচনা করো।
10. README-তে exact seeded credentials লিখো।

## Suggested files

```text
database/seeders/DatabaseSeeder.php
database/seeders/AdminUserSeeder.php
database/seeders/ShopOwnerSeeder.php
database/seeders/ShopSeeder.php
database/seeders/ProductSeeder.php
```

## Acceptance criteria

- `php artisan migrate --seed` সফল।
- README credentials দিয়ে admin login হয়।
- দুই owner দিয়ে আলাদা shop workflow test করা যায়।
- একই global product-এর দুই shop-এ আলাদা price দেখা যায়।
- Unassigned product public view-তে নেই।

## Suggested commit

```text
chore(database): add demo users shops and catalog seeders

Description:
Added reproducible demo data for reviewing admin, owner and customer workflows.

Changes:
- Added admin and shop owner accounts.
- Added sample shops and shared products.
- Added shop-specific product listings with different prices.
- Added an unassigned product for visibility testing.

Files Changed:
- database/seeders/DatabaseSeeder.php
- database/seeders/AdminUserSeeder.php
- database/seeders/ShopOwnerSeeder.php
- database/seeders/ShopSeeder.php
- database/seeders/ProductSeeder.php
```

---

# 14. Phase 11 — README, Fresh Setup & GitHub Submission

## Goal
Reviewer public repository clone করে README অনুসরণ করে application run করতে পারবেন।

## README sections

1. Project name and overview.
2. Features.
3. User roles.
4. Technology stack.
5. Prerequisites and supported versions.
6. Clone repository.
7. Install PHP dependencies.
8. Configure `.env`.
9. Create MySQL database.
10. Generate application key.
11. Run migrations and seeders.
12. Create storage symlink.
13. Install frontend dependencies.
14. Build frontend.
15. Run application.
16. Demo credentials.
17. Public shop URLs.
18. Important business rules.
19. Tests and test command.
20. Known limitations (if any).

## Fresh setup verification commands

```bash
git clone https://github.com/YOUR_USERNAME/shopdeck.git
cd shopdeck

composer install

cp .env.example .env
php artisan key:generate
```

`.env`-এ database settings configure করার পর:

```bash
php artisan migrate --seed
php artisan storage:link

npm install
npm run build

php artisan serve
```

Development mode:

```bash
npm run dev
```

Tests:

```bash
php artisan test
```

Formatting/lint command project-এ configured থাকলে README-তে exact command যোগ করো।

## Fresh setup checklist

1. Existing local database-এর ওপর নির্ভর না করে নতুন empty database ব্যবহার করো।
2. README-র command একে একে চালাও।
3. Seeded admin login করো।
4. Owner 1 login করে catalog থেকে product add করো।
5. Owner 2 login করে একই product add করে আলাদা price দাও।
6. দুই shop-এর public page open করো।
7. Unassigned product hidden কি না দেখো।
8. `npm run build` চালাও।
9. `php artisan test` চালাও।
10. `git status` দেখে `.env`, secrets বা local-only files stage হয়নি নিশ্চিত করো।
11. Public GitHub repository-তে push করো।
12. GitHub থেকে fresh clone করে README follow করে final verification করো।

## Suggested commit

```text
docs: complete project setup and submission documentation

Description:
Documented the application setup, demo accounts and core business rules for fresh installation.

Changes:
- Added step-by-step installation instructions.
- Documented environment and database setup.
- Added demo login credentials.
- Documented sample public shop URLs and test commands.

Files Changed:
- README.md
- .env.example
```

---

# 15. Route Plan

Route structure-এর একটি suggested outline:

```text
Public:
GET  /shops/{slug}

Authentication:
GET  /login
POST /login
POST /logout

Admin:
GET    /admin/dashboard
GET    /admin/shops
GET    /admin/shops/create
POST   /admin/shops
GET    /admin/shops/{shop}/edit
PUT    /admin/shops/{shop}
DELETE /admin/shops/{shop}

GET    /admin/shop-owners
GET    /admin/shop-owners/create
POST   /admin/shop-owners
GET    /admin/shop-owners/{user}/edit
PUT    /admin/shop-owners/{user}

GET    /admin/products
GET    /admin/products/create
POST   /admin/products
GET    /admin/products/{product}/edit
PUT    /admin/products/{product}
DELETE /admin/products/{product}

Shop Owner:
GET    /owner/dashboard
GET    /owner/catalog
POST   /owner/catalog/{product}/add

GET    /owner/products
GET    /owner/products/create
POST   /owner/products
GET    /owner/products/{shopProduct}/edit
PUT    /owner/products/{shopProduct}
DELETE /owner/products/{shopProduct}
```

Actual route methods ও names controller implementation অনুযায়ী adjust করবে। Route model binding ব্যবহার করলে policy/ownership check বাদ দেবে না।

---

# 16. Suggested Frontend Structure

```text
resources/js/
├── Components/
│   ├── AppSidebar.vue
│   ├── AppHeader.vue
│   ├── DataTable.vue
│   ├── ProductCard.vue
│   ├── ProductForm.vue
│   ├── PublicProductCard.vue
│   └── ConfirmDialog.vue
├── Layouts/
│   ├── AdminLayout.vue
│   ├── OwnerLayout.vue
│   └── AuthLayout.vue
└── Pages/
    ├── Admin/
    │   ├── Dashboard.vue
    │   ├── Shops/
    │   ├── ShopOwners/
    │   └── Products/
    ├── Owner/
    │   ├── Dashboard.vue
    │   ├── Catalog/
    │   └── Products/
    ├── Auth/
    └── Public/
        └── Shop.vue
```

UI essentials:
- Separate admin and owner layouts.
- Sidebar navigation.
- Search and pagination.
- Product thumbnail preview.
- Create/edit forms with validation errors.
- Add-to-shop and already-added states.
- Confirmation dialog for destructive actions.
- Responsive public product grid.
- Empty states for no shops/products.

---

# 17. Commit Message Convention

প্রতিটি meaningful phase শেষ হলে commit করবে। Commit message-এ description, changes এবং changed file paths থাকবে।

## Format

```text
type(scope): short summary

Description:
Explain what was implemented and why.

Changes:
- Change one
- Change two
- Change three

Files Changed:
- path/to/file-one
- path/to/file-two
- path/to/file-three
```

## Commit types

| Type | Usage |
|---|---|
| `feat` | New feature |
| `fix` | Bug fix |
| `refactor` | Internal code restructuring |
| `test` | Tests added/updated |
| `docs` | Documentation |
| `style` | Formatting only |
| `perf` | Performance improvement |
| `chore` | Setup, tooling, dependencies |

## Important commit rules

- Description-এ feature-এর উদ্দেশ্য লিখবে।
- Changes-এ actual implementation bullet আকারে লিখবে।
- Files Changed-এ commit-এ সত্যি পরিবর্তিত file path লিখবে।
- Placeholder path (`*`) রেখে final commit message দেবে না; actual path বসাবে।
- এক commit-এ unrelated feature মেশাবে না।
- `.env`, passwords, API keys বা local personal documents commit করবে না।

---

# 18. Three-Day Execution Schedule

Deadline 11 November 2026 ধরে 9–11 November-এর কাজ ভাগ করা হলো। কাজের অগ্রগতি অনুযায়ী phase একাধিক দিনে ছড়াতে পারে।

## Day 1 — 9 November: Foundation & Admin

**Target:** Phase 0–5

- Project setup and repository.
- Authentication and roles.
- Migrations and models.
- Admin shop CRUD.
- Shop owner management.
- Admin product CRUD.
- Admin seeder.

**End-of-day checkpoint:**
- Admin login works.
- Admin shop create/manage করতে পারেন।
- Admin owner create করতে পারেন।
- Admin shared product create/manage করতে পারেন।
- Database migrations and seeders work.

## Day 2 — 10 November: Owner Workflow

**Target:** Phase 6–7

- Owner dashboard.
- Shared catalog browse/search.
- Add catalog product to own shop.
- Duplicate prevention.
- Owner own-product creation.
- Transactional product + listing creation.
- Shop-specific listing edit.
- Price/details/thumbnail override.
- Listing remove/unassign.
- Ownership authorization.

**End-of-day checkpoint:**
- দুই owner একই product নিজেদের shop-এ add করতে পারেন।
- দুই shop-এর price/details/thumbnail আলাদা।
- Owner অন্য shop-এর listing modify করতে পারেন না।

## Day 3 — 11 November: Customer View & Submission

**Target:** Phase 8–11

- Public shop pages.
- Assigned-only product query.
- Active/inactive visibility.
- Feature tests.
- Demo seed data verification.
- README complete.
- Fresh setup test.
- Frontend production build.
- Public GitHub push.
- Final submission.

**End-of-day checkpoint:**
- Fresh clone + README steps দিয়ে project run হয়।
- Demo credentials কাজ করে।
- Customer only assigned products দেখে।
- Core tests pass।
- GitHub repository public এবং complete।

---

# 19. Final Acceptance Checklist

## Authentication
- [ ] Admin login/logout.
- [ ] Shop Owner login/logout.
- [ ] Role-specific route protection.
- [ ] Inactive account access blocked.

## Admin
- [ ] Shop CRUD.
- [ ] Shop Owner create/manage.
- [ ] Owner-to-shop assignment.
- [ ] Global product CRUD.
- [ ] Product creator tracking.

## Shared Catalog
- [ ] Admin product visible in catalog.
- [ ] Owner-created product visible in catalog.
- [ ] Other owners can browse active products.
- [ ] Search and pagination.
- [ ] Add to own shop.
- [ ] Duplicate assignment prevented.

## Shop Listing
- [ ] Shop-specific price.
- [ ] Shop-specific description/details.
- [ ] Shop-specific thumbnail.
- [ ] Listing active/inactive.
- [ ] Listing remove/unassign.
- [ ] Different shops can use different overrides.
- [ ] Owner cannot modify another shop's listing.

## Customer
- [ ] Public shop page.
- [ ] Only assigned products visible.
- [ ] Unassigned products hidden.
- [ ] Inactive listings hidden.
- [ ] Inactive products hidden.
- [ ] Inactive shop unavailable.
- [ ] Correct shop-specific price/image/details.

## Submission
- [ ] Public GitHub repository.
- [ ] README.md complete.
- [ ] `.env.example` committed.
- [ ] Migrations committed.
- [ ] Seeders committed.
- [ ] No secrets committed.
- [ ] Demo credentials work.
- [ ] `npm run build` succeeds.
- [ ] `php artisan test` succeeds.
- [ ] Fresh clone setup verified.

---

# 20. Final Implementation Guidance

সময় কম, তাই feature count বাড়ানোর চেয়ে assignment-এর core workflow নিখুঁত করো।

সবচেয়ে গুরুত্বপূর্ণ তিনটি বিষয়:

1. **Data modeling:** `products` ও `shop_products` আলাদা রাখতে হবে।
2. **Authorization:** Owner শুধু নিজের shop-এর listing modify করতে পারবেন।
3. **Visibility:** Customer শুধু selected shop-এর assigned active products দেখতে পারবেন।

Recommended stack: **Laravel + Vue 3 + Inertia.js + MySQL + Tailwind CSS**.

Recommended project/repository name: **ShopDeck / `shopdeck`**.

প্রথমে Phase 0 থেকে Phase 2 শেষ করো। তারপর Admin CRUD, Owner workflow এবং Customer storefront ক্রমানুসারে complete করো। প্রতিটি phase-এর acceptance criteria verify করে commit দেবে। Cart, checkout, payment, inventory বা analytics-এর মতো unrequested feature যোগ করে deadline risk-এ ফেলবে না।
