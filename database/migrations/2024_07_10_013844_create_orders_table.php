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
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->decimal('total_price', 15, 0);
            $table->foreignId('order_status_id')->constrained('order_statuses');
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->string('receiver_pronvinces');
            $table->string('receiver_district');
            $table->string('receiver_ward');
            $table->string('receiver_address');
            $table->foreignId('payment_status_id')->default(PaymentStatuses::getOrder(PaymentStatuses::PENDING))->constrained('payment_statuses');
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->boolean('pick_up_required')->default(false);
            $table->decimal('discount_price', 15, 0)->nullable();
            $table->string('discount_code')->nullable();
            $table->text('note')->nullable();
            $table->string('sku')->unique();
            $table->softDeletes()->nullable();
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
