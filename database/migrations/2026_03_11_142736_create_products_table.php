<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('categories');
            $table->string('name', 200);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->string('sku', 100)->nullable()->unique();
            $table->text('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();

        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id', 'idx_products_category');
            $table->index('slug', 'idx_products_slug');

            // Index untuk filter status dan performa pencarian
            $table->index('is_active', 'idx_products_active');
            $table->index('price', 'idx_products_price');

            // Composite Index: Sangat berguna untuk query "produk aktif di kategori tertentu"
            $table->index(['category_id', 'is_active'], 'idx_products_cat_active');

            // Index untuk pengurutan dan Soft Deletes
            $table->index('created_at', 'idx_products_created_at');
            $table->index('deleted_at', 'idx_products_deleted_at');
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
