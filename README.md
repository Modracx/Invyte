# Invyte

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/Modracx/Invyte)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Status](https://img.shields.io/badge/status-stable-success.svg)](https://github.com/Modracx/Invyte)
[![Backend](https://img.shields.io/badge/backend-Lumen%2010-red.svg)](https://github.com/Modracx/Invyte)
[![Frontend](https://img.shields.io/badge/frontend-Vue%203%2B%2B-brightgreen.svg)](https://github.com/Modracx/Invyte)

**Professional inventory management made simple.** Invyte is a modern, full-featured inventory management system designed specifically for Magento stores. Built with **Lumen 10** for a fast, lightweight REST API backend and **Vue 3 + Vite + Tailwind CSS** for an intuitive, reactive frontend. Invyte gives store owners and managers full control over their inventory with speed, simplicity, and enterprise-grade features.

## Table of Contents
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Quick Start](#quick-start)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [CLI Commands](#cli-commands)
- [API Reference](#api-reference)
- [Frontend Development](#frontend-development)
- [Database](#database)
- [User Roles](#user-roles)
- [Troubleshooting](#troubleshooting)
- [License](#license)

---

## Features

### 📦 Inventory Management
- Multi-warehouse stock tracking and management
- Real-time stock level monitoring across all sources
- Low-stock alerts with customizable thresholds
- Complete audit log of all stock movements
- Inline stock quantity editing per warehouse

### 📋 Purchase Orders
- Create and manage purchase orders with multiple items
- Track PO status (draft, pending, received, cancelled)
- Receive stock against POs with partial fulfillment support
- Full PO history and tracking

### 📊 Reporting & Analytics
- Dashboard with key metrics (total products, low stock count, active warehouses, pending POs)
- Low Stock Report with customizable thresholds
- Stock Value by Warehouse report
- Movement Summary with visual charts (ApexCharts)
- CSV export capabilities

### 👥 User Management
- Role-based access control (Admin, Manager)
- Scoped manager access to assigned warehouses
- CLI-based user creation and management
- User assignment to warehouse sources

### 🏢 Warehouse Management
- CRUD operations for warehouse/source definitions
- Multi-source inventory support
- Warehouse-level stock views and reports

### 🤝 Supplier Management
- Vendor/supplier records for purchase orders
- Supplier contact and pricing information
- Supplier history tracking

### 🎨 User Interface
- Fully responsive and mobile-friendly design
- Modern Vue 3 + Tailwind CSS frontend
- Intuitive dashboard and navigation
- Real-time data updates with Pinia state management
- ApexCharts for data visualization

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend API | Lumen 10 (PHP micro-framework) |
| Authentication | JWT via `firebase/php-jwt` (8-hour tokens) |
| Frontend | Vue 3 + Vite + Pinia + Vue Router 4 |
| Styling | Tailwind CSS 3 + @tailwindcss/forms |
| Charts | ApexCharts (vue3-apexcharts) |
| Database | Magento 2 MySQL (reads + writes existing Magento tables + 8 new Invyte tables) |
| Build Tool | Vite (fast frontend bundler) |
| HTTP Client | Axios with JWT interceptor |

---

## Quick Start

1. **Clone/Copy**: Copy the `ims/` folder into your Magento root
   ```
   /path/to/magento/ims/
   ```

2. **Install Dependencies**:
   ```bash
   cd /path/to/magento/ims
   composer install
   npm install
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   # Edit .env with your database and JWT settings
   ```

4. **Setup Database**:
   ```bash
   php ims/cli migrate
   php ims/cli user:create --name="Administrator" --email="admin@example.com" --password="yourpassword" --role=admin
   ```

5. **Build Frontend**:
   ```bash
   npm run build
   ```

6. **Configure Web Server**: See [Installation](#installation) section

7. **Access**: `http://yourdomain.com/ims`

---

## Requirements

| Requirement | Minimum Version |
|-------------|----------------|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 18+ |
| npm | 9+ |
| MySQL | 5.7+ / MariaDB 10.3+ |
| Apache | 2.4+ with `mod_rewrite` |
| Magento | 2.4.x |

### PHP Extensions
- pdo_mysql
- mbstring
- openssl
- json
- xml
- curl

---

## Installation

### Option 1: Apache (Recommended - Symlink Setup)

This setup uses a symlink from Magento's `pub/` directory to `ims/public/`, requiring no VirtualHost changes.

```bash
# From Magento root
ln -sf /path/to/magento/ims/public /path/to/magento/pub/ims

# Install dependencies
cd /path/to/magento/ims
composer install
npm install

# Copy and configure environment
cp .env.example .env
# Edit .env with your database and JWT settings

# Run migrations
php ims/cli migrate

# Create first admin user
php ims/cli user:create --name="Administrator" --email="admin@example.com" --password="password" --role=admin

# Build frontend
npm run build

# Access: http://yourdomain.com/ims/
```

### Option 2: Nginx

Add to your Magento server block:

```nginx
location /ims/ {
    alias /path/to/magento/ims/public/;
    try_files $uri $uri/ /ims/index.php?$query_string;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $request_filename;
        include fastcgi_params;
    }
}
```

### Option 3: Standalone VirtualHost (Apache)

```apache
<VirtualHost *:80>
    ServerName ims.yourdomain.com
    DocumentRoot /path/to/magento/ims/public

    <Directory /path/to/magento/ims/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.4-fpm.sock|fcgi://localhost"
    </FilesMatch>
</VirtualHost>
```

---

## Configuration

### Environment Variables (.env)

```ini
# Application
APP_ENV=production           # local | production
APP_DEBUG=false              # Set true only in development
APP_KEY=base64:CHANGE_ME     # Generate with: php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
APP_TIMEZONE=UTC
APP_URL=http://yourdomain.com/ims

# Database (same as Magento's)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_magento_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error              # Use 'debug' in development

# Cache / Queue
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

# JWT Authentication
JWT_SECRET=CHANGE_THIS_TO_A_LONG_RANDOM_STRING_AT_LEAST_32_CHARS
JWT_TTL=480                  # Token lifetime in minutes (480 = 8 hours)

# Invyte Settings
IMS_LOW_STOCK_THRESHOLD=10   # Default low-stock threshold

# Magento EAV Attribute IDs
MAGENTO_ATTR_NAME=73
MAGENTO_ATTR_PRICE=77
MAGENTO_ATTR_COST=81
```

**Generate Secure Keys:**
```bash
# APP_KEY
php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"

# JWT_SECRET
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

---

## CLI Commands

All commands run from the Magento root directory:

```bash
php ims/cli <command> [arguments]
```

### User Management

```bash
# Create user
php ims/cli user:create --name="John Doe" --email="john@example.com" --password="secret" --role=admin

# Assign manager to warehouse
php ims/cli user:assign john@example.com warehouse-east

# Update user
php ims/cli user:update john@example.com --password="newpassword"
php ims/cli user:update john@example.com --role=manager

# Deactivate/Activate
php ims/cli user:update john@example.com --deactivate
php ims/cli user:update john@example.com --activate

# List all users
php ims/cli user:list
```

### Inventory Management

```bash
# Run database migrations
php ims/cli migrate

# Sync stock with Magento
php ims/cli stock:sync

# Check for low-stock items
php ims/cli stock:check-low
php ims/cli stock:check-low --threshold=5
```

### Reports

```bash
# Export inventory to CSV
php ims/cli report:export csv
# Output: ims/storage/exports/inventory_YYYY-MM-DD_HHmmss.csv
```

---

## API Reference

**Base URL**: `http://yourdomain.com/ims/api`

All endpoints except `/auth/login` require:
```
Authorization: Bearer <jwt_token>
```

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/login` | Login — returns JWT token |
| POST | `/auth/logout` | Logout |
| GET | `/auth/me` | Get current user info |

**Login:**
```json
POST /auth/login
{ "email": "admin@example.com", "password": "yourpassword" }

Response:
{ "token": "eyJ...", "user": { "id": 1, "name": "Administrator", "email": "admin@example.com", "role": "admin" } }
```

### Products

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/products` | List products with stock |
| GET | `/products/{id}` | Product detail |
| PUT | `/products/{id}/stock` | Update stock quantity |

**Query Parameters (GET /products):**
- `search` — Filter by SKU or name
- `low_stock=1` — Show only low-stock items
- `per_page` — Items per page (max 100)
- `page` — Page number

### Sources / Warehouses

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/sources` | All | List sources |
| POST | `/sources` | Admin | Create source |
| PUT | `/sources/{code}` | Admin | Update source |
| DELETE | `/sources/{code}` | Admin | Delete source |

### Stock Movements

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/movements` | Paginated movement log |
| POST | `/movements` | Record adjustment |

### Purchase Orders

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/purchase-orders` | List POs |
| POST | `/purchase-orders` | Create PO |
| GET | `/purchase-orders/{id}` | PO detail |
| PUT | `/purchase-orders/{id}/receive` | Receive stock |

### Reports & Alerts

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/reports/low-stock` | Low stock report |
| GET | `/reports/stock-value` | Stock value by warehouse |
| GET | `/reports/movements` | Movement summary |
| GET | `/alerts` | Active alerts |
| PUT | `/alerts/{id}/resolve` | Resolve alert |

---

## Frontend Development

### Development Server (Hot Reload)

```bash
cd ims
npm run dev
```

Vite dev server runs on port 5173 with hot module replacement (HMR).

Update proxy target in `vite.config.js` if needed:
```js
proxy: {
    '/api': {
        target: 'http://yourdomain.com/ims',
        changeOrigin: true
    }
}
```

### Production Build

```bash
cd ims
npm run build
```

Output: `ims/public/assets/` (optimized JS, CSS, and manifest)

### Adding a New Page

1. Create `resources/js/views/YourPage.vue`
2. Add route in `resources/js/router/index.js`
3. Update nav in `resources/js/components/Layout/AppSidebar.vue`
4. Run `npm run build`

---

## Database

### New Invyte Tables

| Table | Purpose |
|-------|---------|
| `ims_migrations` | Migration tracking |
| `ims_users` | User accounts and authentication |
| `ims_user_source_assignments` | Manager → warehouse assignments |
| `ims_suppliers` | Vendor/supplier records |
| `ims_purchase_orders` | PO headers |
| `ims_purchase_order_items` | PO line items |
| `ims_stock_movements` | Complete stock audit log |
| `ims_alerts` | Low-stock alerts |

### Magento Tables Used (Read + Write)

| Table | Usage |
|-------|-------|
| `catalog_product_entity` | Product list |
| `catalog_product_entity_varchar` | Product names |
| `catalog_product_entity_decimal` | Prices and costs |
| `cataloginventory_stock_item` | Legacy stock quantities |
| `inventory_source` | Warehouse definitions |
| `inventory_source_item` | Per-source stock levels |
| `inventory_stock` | Stock aggregations |

---

## User Roles

### Admin
- Full access to all features
- Can manage sources (warehouses) and suppliers
- Can view/edit stock across all warehouses
- Can manage users via CLI
- Full access to reports and alerts

### Manager
- Scoped to assigned warehouses
- Can only view/edit stock for assigned sources
- Can create/receive POs for assigned warehouses
- Cannot manage sources, suppliers, or users
- Reports filtered to assigned warehouses

**Assign Manager to Warehouse:**
```bash
php ims/cli user:assign manager@example.com warehouse-east
```

---

## Troubleshooting

### 403 Forbidden on /ims

Verify `ims/.htaccess` contains:
```apache
Options -Indexes
```

### JWT Token Expired (401)

Token lifetime is controlled by `JWT_TTL` in `.env`. Default is 480 minutes (8 hours). Frontend auto-redirects to login on 401.

### Stock Not Syncing

Reconcile stock with Magento:
```bash
php ims/cli stock:sync
```

### Product Names Showing as Null

Verify `MAGENTO_ATTR_NAME` in `.env` matches your Magento installation.

### View Application Logs

```bash
tail -f ims/storage/logs/lumen-$(date +%Y-%m-%d).log
```

### Rebuild Frontend

```bash
cd ims && npm run build
```

---

## Directory Structure

```
ims/
├── app/
│   ├── Console/Commands/        # CLI commands
│   ├── Http/Controllers/        # API controllers
│   ├── Http/Middleware/         # JWT, CORS, Role middleware
│   └── Models/                  # Eloquent models
├── bootstrap/
│   └── app.php                  # Lumen bootstrap
├── cli                          # CLI entry point
├── database/
│   └── migrations/              # 8 IMS migrations
├── public/
│   ├── index.php                # API router + SPA shell
│   ├── spa.html.php             # SPA HTML with asset injection
│   ├── .htaccess                # Apache rewrite rules
│   └── assets/                  # Compiled frontend
├── resources/
│   └── js/                      # Vue 3 source
│       ├── api/index.js         # Axios + JWT interceptor
│       ├── router/index.js      # Vue Router with guards
│       ├── store/auth.js        # Pinia auth store
│       ├── components/          # Reusable components
│       └── views/               # Page components
├── routes/
│   └── api.php                  # API routes
├── storage/
│   ├── logs/                    # Application logs
│   └── exports/                 # CSV exports
├── vendor/                      # Composer packages
├── node_modules/                # npm packages
├── .env                         # Environment config
├── .env.example                 # Environment template
├── composer.json
├── package.json
├── vite.config.js
├── tailwind.config.js
└── postcss.config.js
```

---

## License

Invyte is provided under the MIT License. See the [LICENSE](LICENSE) file for details.
