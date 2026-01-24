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
        Schema::table('trainer_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('trainer_bookings', 'trainer_package_id')) {
                $table->unsignedBigInteger('trainer_package_id')->nullable();
            }
        });

        Schema::table('trainer_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('trainer_bookings', 'trainer_package_id')) {
                $table->foreign('trainer_package_id')
                    ->references('id')
                    ->on('trainer_packages')
                    ->nullOnDelete();
            }
        });

        Schema::table('trainer_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('trainer_bookings', 'session_datetime')) {
                $table->dropColumn('session_datetime');
            }

            if (Schema::hasColumn('trainer_bookings', 'duration_minutes')) {
                $table->dropColumn('duration_minutes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainer_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('trainer_bookings', 'session_datetime')) {
                $table->timestamp('session_datetime')->nullable();
            }

            if (! Schema::hasColumn('trainer_bookings', 'duration_minutes')) {
                $table->integer('duration_minutes')->default(60);
            }
        });

        Schema::table('trainer_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('trainer_bookings', 'trainer_package_id')) {
                $table->dropForeign(['trainer_package_id']);
                $table->dropColumn('trainer_package_id');
            }
        });
    }
};
