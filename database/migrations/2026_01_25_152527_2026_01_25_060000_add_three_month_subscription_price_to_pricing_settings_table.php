<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
         if (!Schema::hasColumn('pricing_settings', 'three_month_subscription_price')) {
            Schema::table('pricing_settings', function (Blueprint $table) {
                $table->decimal('three_month_subscription_price', 10, 2)
                    ->default(240000)
                    ->after('monthly_subscription_price');
            });
        }
    }

    public function down(): void
    {
           if (Schema::hasColumn('pricing_settings', 'three_month_subscription_price')) {
            Schema::table('pricing_settings', function (Blueprint $table) {
                $table->dropColumn('three_month_subscription_price');
            });
        }
    }
};
