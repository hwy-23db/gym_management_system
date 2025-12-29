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
            $table->boolean('is_on_hold')->default(false)->after('is_expired');
            $table->date('hold_started_at')->nullable()->after('is_on_hold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_memberships', function (Blueprint $table) {
            $table->dropColumn(['is_on_hold', 'hold_started_at']);
        });
    }
};
