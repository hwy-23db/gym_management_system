<?php

namespace Database\Seeders;

use App\Models\TrainerPackage;
use Illuminate\Database\Seeder;

/**
 * Example seeder showing how to structure trainer package data
 * based on the UNITY FITNESS pricing sheet.
 * 
 * NOTE: All trainers share the same package prices, so packages are global.
 * 
 * This is an EXAMPLE file - you can use this as a reference
 * or create an actual seeder from this structure.
 */
class TrainerPackageSeederExample extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This example shows how to structure the data from the pricing sheet:
     * 
     * Session Package:
     * - 10 Sessions: 300,000 Ks
     * - 20 Sessions: 580,000 Ks
     * - 30 Sessions: 840,000 Ks
     * - 40 Sessions: 1,080,000 Ks
     * - 60 Sessions: 1,560,000 Ks
     * 
     * Monthly Package:
     * - 1 Month: 400,000 Ks
     * - 2 Months: 780,000 Ks
     * - 3 Months: 1,140,000 Ks
     * - 6 Months: 2,220,000 Ks
     * 
     * Duo Package:
     * - 10 Sessions: 540,000 Ks
     * - 20 Sessions: 1,060,000 Ks
     * - 30 Sessions: 1,520,000 Ks
     * - 1 Month: 740,000 Ks
     * - 2 Months: 1,460,000 Ks
     * - 3 Months: 2,120,000 Ks
     */
    public function run(): void
    {
        $packages = [
            // SESSION PACKAGES
            [
                'package_type' => 'session',
                'quantity' => 10,
                'duration_unit' => 'sessions',
                'price' => 300000.00,
                'name' => '10 Sessions Package',
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'package_type' => 'session',
                'quantity' => 20,
                'duration_unit' => 'sessions',
                'price' => 580000.00,
                'name' => '20 Sessions Package',
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'package_type' => 'session',
                'quantity' => 30,
                'duration_unit' => 'sessions',
                'price' => 840000.00,
                'name' => '30 Sessions Package',
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'package_type' => 'session',
                'quantity' => 40,
                'duration_unit' => 'sessions',
                'price' => 1080000.00,
                'name' => '40 Sessions Package',
                'display_order' => 4,
                'is_active' => true,
            ],
            [
                'package_type' => 'session',
                'quantity' => 60,
                'duration_unit' => 'sessions',
                'price' => 1560000.00,
                'name' => '60 Sessions Package',
                'display_order' => 5,
                'is_active' => true,
            ],

            // MONTHLY PACKAGES
            [
                'package_type' => 'monthly',
                'quantity' => 1,
                'duration_unit' => 'months',
                'price' => 400000.00,
                'name' => '1 Month Package',
                'display_order' => 10,
                'is_active' => true,
            ],
            [
                'package_type' => 'monthly',
                'quantity' => 2,
                'duration_unit' => 'months',
                'price' => 780000.00,
                'name' => '2 Months Package',
                'display_order' => 11,
                'is_active' => true,
            ],
            [
                'package_type' => 'monthly',
                'quantity' => 3,
                'duration_unit' => 'months',
                'price' => 1140000.00,
                'name' => '3 Months Package',
                'display_order' => 12,
                'is_active' => true,
            ],
            [
                'package_type' => 'monthly',
                'quantity' => 6,
                'duration_unit' => 'months',
                'price' => 2220000.00,
                'name' => '6 Months Package',
                'display_order' => 13,
                'is_active' => true,
            ],

            // DUO PACKAGES (Session-based)
            [
                'package_type' => 'duo',
                'quantity' => 10,
                'duration_unit' => 'sessions',
                'price' => 540000.00,
                'name' => 'Duo 10 Sessions Package',
                'display_order' => 20,
                'is_active' => true,
            ],
            [
                'package_type' => 'duo',
                'quantity' => 20,
                'duration_unit' => 'sessions',
                'price' => 1060000.00,
                'name' => 'Duo 20 Sessions Package',
                'display_order' => 21,
                'is_active' => true,
            ],
            [
                'package_type' => 'duo',
                'quantity' => 30,
                'duration_unit' => 'sessions',
                'price' => 1520000.00,
                'name' => 'Duo 30 Sessions Package',
                'display_order' => 22,
                'is_active' => true,
            ],

            // DUO PACKAGES (Monthly-based)
            [
                'package_type' => 'duo',
                'quantity' => 1,
                'duration_unit' => 'months',
                'price' => 740000.00,
                'name' => 'Duo 1 Month Package',
                'display_order' => 30,
                'is_active' => true,
            ],
            [
                'package_type' => 'duo',
                'quantity' => 2,
                'duration_unit' => 'months',
                'price' => 1460000.00,
                'name' => 'Duo 2 Months Package',
                'display_order' => 31,
                'is_active' => true,
            ],
            [
                'package_type' => 'duo',
                'quantity' => 3,
                'duration_unit' => 'months',
                'price' => 2120000.00,
                'name' => 'Duo 3 Months Package',
                'display_order' => 32,
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            TrainerPackage::create($package);
        }

        $this->command->info('Trainer packages seeded successfully!');
    }
}
