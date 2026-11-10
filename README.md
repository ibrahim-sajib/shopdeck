# ShopDeck

A multi-shop e-commerce management system with a shared product catalog.

## Overview

ShopDeck allows administrators to maintain a centralized product catalog and manage shops and shop owners. Shop owners can browse the shared catalog, add products to their shops, customize listing prices, descriptions, and thumbnails, or create their own products. Public storefronts isolate listings per shop, ensuring customers only see active products explicitly assigned to each shop.

## Technology Stack

- **Backend:** Laravel (PHP 8.3+)
- **Frontend:** Vue 3, Inertia.js, Tailwind CSS
- **Database:** SQLite / MySQL compatible relational database
- **Asset Bundler:** Vite

## Initial Setup

1. Copy `.env.example` to `.env` and configure your environment.
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Generate app key and run migrations and seed demo user:
   ```bash
   php artisan key:generate
   php artisan migrate:fresh --seed 
   php artisan storage:link
   ```
4. Run shopdeck:
   ```bash
   php artisan serve
   npm run run dev
   ```
