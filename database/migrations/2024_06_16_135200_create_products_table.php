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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->text('thumbnail');
            $table->string('name');
            $table->text('content');
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('brand_id')->constrained('brands');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_hot_deal')->default(true);
            $table->boolean('is_good_deal')->default(false);
            $table->boolean('is_new')->default(true);
            $table->boolean('is_show_home')->default(false);
            $table->string('type_discount')->nullable();
            $table->decimal('discount')->nullable();
            $table->unsignedBigInteger('total_review')->nullable();
            $table->unsignedBigInteger('avg_stars')->nullable();
            $table->text('public_id');
            $table->string('slug');
            $table->softDeletes('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
