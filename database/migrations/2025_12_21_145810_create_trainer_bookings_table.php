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
        Schema::create('trainer_bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('trainer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('trainer_package_id')->nullable();

            $table->integer('sessions_count')->default(1);

            $table->decimal('price_per_session', 10, 2);
            $table->decimal('total_price', 10, 2);

            $table->string('status')->default('pending');
            $table->string('paid_status')->default('unpaid');

            $table->text('notes')->nullable();
            $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_bookings');
    }
};
