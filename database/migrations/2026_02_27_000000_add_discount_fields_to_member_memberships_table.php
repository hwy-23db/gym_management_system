<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('member_memberships', function (Blueprint $table) {
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('membership_plan_id');
            $table->decimal('final_price', 10, 2)->after('discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_memberships', function (Blueprint $table) {
            $table->dropColumn(['discount_percentage', 'final_price']);
        });
    }
};
