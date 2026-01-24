# Trainer Packages Table Structure

## Overview

The `trainer_packages` table stores **global pricing packages** that apply to all trainers (since all trainers share the same prices). It supports three different package types as shown in the UNITY FITNESS pricing sheet:

1. **Session Package** - Packages based on number of sessions
2. **Monthly Package** - Packages based on monthly duration
3. **Duo Package** - Packages for two people (can be session or monthly based)

**Note:** Since all trainers have the same package prices, packages are **global** (no `trainer_id` field).

## Database Schema

### Table: `trainer_packages`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `package_type` | enum | One of: `'session'`, `'monthly'`, `'duo'` |
| `quantity` | integer | Number of sessions or months |
| `duration_unit` | string | Either `'sessions'` or `'months'` |
| `price` | decimal(12,2) | Total price for the package |
| `name` | string | Optional package name/description |
| `display_order` | integer | Order for display purposes |
| `is_active` | boolean | Whether the package is active |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

**Unique Constraint:** `package_type`, `quantity`, and `duration_unit` combination must be unique.

## Data Structure Examples

### Session Packages
```php
[
    'package_type' => 'session',
    'quantity' => 10,
    'duration_unit' => 'sessions',
    'price' => 300000.00,
    'name' => '10 Sessions Package',
]
```

### Monthly Packages
```php
[
    'package_type' => 'monthly',
    'quantity' => 1,
    'duration_unit' => 'months',
    'price' => 400000.00,
    'name' => '1 Month Package',
]
```

### Duo Packages
```php
// Duo Session Package
[
    'package_type' => 'duo',
    'quantity' => 10,
    'duration_unit' => 'sessions',
    'price' => 540000.00,
    'name' => 'Duo 10 Sessions Package',
]

// Duo Monthly Package
[
    'package_type' => 'duo',
    'quantity' => 1,
    'duration_unit' => 'months',
    'price' => 740000.00,
    'name' => 'Duo 1 Month Package',
]
```

## Usage Examples

### Creating Packages

```php
use App\Models\TrainerPackage;

// Packages are global - no trainer_id needed
TrainerPackage::create([
    'package_type' => 'session',
    'quantity' => 10,
    'duration_unit' => 'sessions',
    'price' => 300000.00,
    'name' => '10 Sessions Package',
    'display_order' => 1,
    'is_active' => true,
]);
```

### Querying Packages

```php
use App\Models\TrainerPackage;

// Get all active packages (global - applies to all trainers)
$packages = TrainerPackage::active()
    ->orderBy('display_order')
    ->get();

// Get only session packages
$sessionPackages = TrainerPackage::ofType('session')
    ->active()
    ->orderBy('quantity')
    ->get();

// Get only monthly packages
$monthlyPackages = TrainerPackage::ofType('monthly')
    ->active()
    ->orderBy('quantity')
    ->get();

// Get duo packages
$duoPackages = TrainerPackage::ofType('duo')
    ->active()
    ->orderBy('display_order')
    ->get();
```

## Complete Pricing Sheet Data

Based on the UNITY FITNESS pricing sheet, here's the complete data structure:

### Session Packages
- 10 Sessions: 300,000 Ks
- 20 Sessions: 580,000 Ks
- 30 Sessions: 840,000 Ks
- 40 Sessions: 1,080,000 Ks
- 60 Sessions: 1,560,000 Ks

### Monthly Packages
- 1 Month: 400,000 Ks
- 2 Months: 780,000 Ks
- 3 Months: 1,140,000 Ks
- 6 Months: 2,220,000 Ks

### Duo Packages
**Sessions:**
- 10 Sessions: 540,000 Ks
- 20 Sessions: 1,060,000 Ks
- 30 Sessions: 1,520,000 Ks

**Months:**
- 1 Month: 740,000 Ks
- 2 Months: 1,460,000 Ks
- 3 Months: 2,120,000 Ks

## Seeding Data

See `database/seeders/TrainerPackageSeederExample.php` for a complete example of how to seed all packages from the pricing sheet.

To use it:
1. Copy the seeder to `database/seeders/TrainerPackageSeeder.php`
2. Remove "Example" from the class name
3. Run: `php artisan db:seed --class=TrainerPackageSeeder`

## Migration

Run the migration to create the table:
```bash
php artisan migrate
```

## About `trainer_pricing` Table

**Question:** Do you still need the `trainer_pricing` table?

Since all trainers share the same package prices, the `trainer_pricing` table (which stores individual `price_per_session` per trainer) might not be needed if:

- ✅ All pricing goes through packages
- ✅ You don't need individual session pricing outside of packages
- ✅ All trainers use the same pricing structure

**However**, you might want to keep `trainer_pricing` if:

- ⚠️ You need a fallback/default price for individual sessions
- ⚠️ You have legacy code that still uses `price_per_session`
- ⚠️ You want to support custom pricing per trainer in the future

**Recommendation:** If everything is package-based, you can remove or deprecate the `trainer_pricing` table. If you need a default price, consider adding a single global setting instead of per-trainer pricing.
