<?php

use App\Enums\PaymentStatuses;
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
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('total_price');
            $table->foreignId('status_id')->constrained('order_status');
            $table->string('receiver_name');
            $table->string('receiver_email');
            $table->string('receiver_phone');
            $table->string('receiver_city');
            $table->string('receiver_county');
            $table->string('receiver_district');
            $table->string('receiver_address');
            $table->integer('payment_status')->default(PaymentStatuses::PENDING);
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->boolean('pick_up_required')->default(false);
            $table->decimal('discount_price')->nullable();
            $table->string('discount_code')->nullable();
            $table->text('note')->nullable();
            $table->string('sku');
            $table->timestamps();
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
