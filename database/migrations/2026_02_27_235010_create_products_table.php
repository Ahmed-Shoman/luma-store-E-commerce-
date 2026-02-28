<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();

            // الحقول الجديدة التي طلبتها
            $table->enum('style', ['mini', 'midi', 'maxi'])->nullable();
            $table->boolean('is_best_seller')->default(false);
            $table->boolean('is_trending_now')->default(false);
            $table->boolean('is_new_arrival')->default(true); // خليناها true افتراضيا عشان أي منتج جديد ينزل في القسم ده فورا

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('products');
    }
};