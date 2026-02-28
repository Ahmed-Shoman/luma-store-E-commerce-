<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('governorate')->nullable(); // المحافظة لحساب الشحن
            $table->text('shipping_address');

            // حالة الطلب
            $table->enum('status', ['pending', 'processing', 'shipped', 'completed', 'cancelled'])->default('pending');

            $table->decimal('total_amount', 10, 2)->default(0); // إجمالي الفاتورة
            $table->text('notes')->nullable(); // ملاحظات العميل

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('orders');
    }
};
