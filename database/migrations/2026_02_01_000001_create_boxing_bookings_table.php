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
        Schema::create('boxing_bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('trainer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('boxing_package_id')->nullable();
            $table->foreign('boxing_package_id')
                ->references('id')
                ->on('boxing_packages')
                ->nullOnDelete();

            $table->unsignedInteger('sessions_count')->default(1);
            $table->unsignedInteger('sessions_remaining')->default(0);
            $table->dateTime('sessions_start_date')->nullable();
            $table->dateTime('sessions_end_date')->nullable();
            $table->dateTime('month_start_date')->nullable();
            $table->dateTime('month_end_date')->nullable();
            $table->dateTime('hold_start_date')->nullable();
            $table->dateTime('hold_end_date')->nullable();
            $table->unsignedInteger('total_hold_days')->default(0);

            $table->decimal('price_per_session', 10, 2);
            $table->decimal('total_price', 10, 2);

            $table->string('status')->default('pending');
            $table->string('paid_status')->default('unpaid');
            $table->timestamp('paid_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boxing_bookings');
    }
};
