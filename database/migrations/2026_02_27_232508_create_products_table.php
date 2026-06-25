<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name_ar');
            $table->string('name_en');

            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();

            $table->decimal('price', 10, 2)->index();
            $table->decimal('original_price', 10, 2)->nullable();

            $table->unsignedBigInteger('category_id');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();

            $table->boolean('is_best_seller')->default(false);
            $table->boolean('is_new_arrival')->default(true);
            $table->boolean('is_trending')->default(false);

            // Reel Video
            $table->string('reel_video')->nullable();

            $table->timestamps();

            $table->index('category_id');
            $table->index('is_trending');
            $table->index('is_new_arrival');
            $table->index('is_best_seller');
        });
    }



    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
