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
        Schema::table('boxing_bookings', function (Blueprint $table) {
            // Drop existing foreign keys
            $table->dropForeign('boxing_bookings_member_id_foreign');
            $table->dropForeign('boxing_bookings_trainer_id_foreign');
            
            // Recreate foreign keys pointing to users table
            $table->foreign('member_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('trainer_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boxing_bookings', function (Blueprint $table) {
            // Drop users table foreign keys
            $table->dropForeign('boxing_bookings_member_id_foreign');
            $table->dropForeign('boxing_bookings_trainer_id_foreign');
            
            // Recreate foreign keys pointing to members and trainers tables
            $table->foreign('member_id')->references('id')->on('members')->cascadeOnDelete();
            $table->foreign('trainer_id')->references('id')->on('trainers')->cascadeOnDelete();
        });
    }
};
