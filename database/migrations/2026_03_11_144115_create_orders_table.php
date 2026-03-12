<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number', 32)->unique();
            $table->uuid('user_id');
            $table->string('status', 16)->default('PENDING');
            $table->string('payment_method', 32)->nullable();
            $table->string('payment_status', 16)->default('UNPAID');
            $table->jsonb('address_snapshot');
            $table->decimal('subtotal_price', 12, 2)->default(0);
            $table->decimal('discount_price', 12, 2)->default(0);
            $table->decimal('shipping_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->string('note', 255)->nullable();
            $table->timestamp('placed_at')->useCurrent();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason', 100)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('receipt_no', 50)->nullable()->unique();
            $table->string('snap_token', 255)->nullable();
            $table->string('snap_redirect_url', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->uuid('product_id');
            $table->string('name_snapshot', 200);
            $table->decimal('unit_price', 12, 2);
            $table->integer('quantity');
            $table->decimal('total_price', 12, 2);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_orders_user_status');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id', 'idx_order_items_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
