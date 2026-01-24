<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasColumn('trainer_bookings', 'session_datetime')) {
            DB::statement('ALTER TABLE trainer_bookings MODIFY session_datetime DATETIME NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasColumn('trainer_bookings', 'session_datetime')) {
            DB::statement('ALTER TABLE trainer_bookings MODIFY session_datetime TIMESTAMP NOT NULL');
        }
    }
};
