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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Customer snapshot
            $table->string('customer_name')->index();
            $table->string('customer_phone', 20)->index();
            $table->text('customer_address')->nullable();

            // Financial
            $table->unsignedInteger('total');

            // Status
            $table->enum('status', [
                'pending',
                'processing',
                'shipped',
                'delivered',
                'cancelled'
            ])->default('pending')->index();


            $table->timestamps();

            // Important indexes
            $table->index(['status', 'created_at']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
