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
            $table->decimal('discount')->nullable();
            $table->string('type_discount')->nullable();
            $table->unsignedBigInteger('total_review')->default(0)->nullable();
            $table->unsignedBigInteger('avg_stars')->default(0)->nullable();
            $table->boolean('active')->default(true);
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
