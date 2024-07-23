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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->integer('quantity')->default(1);//số lượng mã giảm giá
            $table->integer('value')->nullable(); //Loại giảm giá
            $table->enum('type', ['number', 'percent', 'free_ship']);
            $table->dateTime('start_date'); //thời gian bắt đầu phải lớn hơn thời gian hiện tại
            $table->integer('used_count')->default(0);
            $table->dateTime('end_date'); // thời gian kết thúc phải lớn hơn thời gian bắt đầu
            $table->integer('discount_max'); //chỉ giảm tối đa được nhiêu đây
            $table->integer('is_activate')->default(1);
            $table->enum('status', ['public', 'private'])->default('public');
            $table->softDeletes()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
