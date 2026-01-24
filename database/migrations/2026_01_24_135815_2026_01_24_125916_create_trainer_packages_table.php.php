<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trainer_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('package_type');
            $table->unsignedInteger('sessions_count')->nullable();
            $table->unsignedInteger('duration_months')->nullable();
            $table->decimal('price', 12, 2);
            $table->timestamps();
        });

        $now = now();

        DB::table('trainer_packages')->insert([
            [
                'name' => '10 Sessions',
                'package_type' => 'personal',
                'sessions_count' => 10,
                'duration_months' => null,
                'price' => 300000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '20 Sessions',
                'package_type' => 'personal',
                'sessions_count' => 20,
                'duration_months' => null,
                'price' => 580000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '30 Sessions',
                'package_type' => 'personal',
                'sessions_count' => 30,
                'duration_months' => null,
                'price' => 840000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '40 Sessions',
                'package_type' => 'personal',
                'sessions_count' => 40,
                'duration_months' => null,
                'price' => 1080000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '60 Sessions',
                'package_type' => 'personal',
                'sessions_count' => 60,
                'duration_months' => null,
                'price' => 1560000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '1 Month',
                'package_type' => 'monthly',
                'sessions_count' => null,
                'duration_months' => 1,
                'price' => 400000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '2 Months',
                'package_type' => 'monthly',
                'sessions_count' => null,
                'duration_months' => 2,
                'price' => 780000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '3 Months',
                'package_type' => 'monthly',
                'sessions_count' => null,
                'duration_months' => 3,
                'price' => 1140000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '6 Months',
                'package_type' => 'monthly',
                'sessions_count' => null,
                'duration_months' => 6,
                'price' => 2220000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '10 Sessions',
                'package_type' => 'duo',
                'sessions_count' => 10,
                'duration_months' => null,
                'price' => 540000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '20 Sessions',
                'package_type' => 'duo',
                'sessions_count' => 20,
                'duration_months' => null,
                'price' => 1060000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '30 Sessions',
                'package_type' => 'duo',
                'sessions_count' => 30,
                'duration_months' => null,
                'price' => 1520000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '1 Month',
                'package_type' => 'duo',
                'sessions_count' => null,
                'duration_months' => 1,
                'price' => 740000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '2 Months',
                'package_type' => 'duo',
                'sessions_count' => null,
                'duration_months' => 2,
                'price' => 1460000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '3 Months',
                'package_type' => 'duo',
                'sessions_count' => null,
                'duration_months' => 3,
                'price' => 2120000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_packages');
    }
};
