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
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->boolean('is_verified_purchase')->default(true);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();

            // --- CONSTRAINT & INDEX DI AKHIR ---

            // Unique: Satu user hanya boleh review satu produk satu kali
            $table->unique(['user_id', 'product_id'], 'uniq_user_product_review');

            // Partial Indexes (Optimasi PostgreSQL untuk Soft Deletes)
            $table->index('product_id', 'idx_reviews_product')->whereNull('deleted_at');
            $table->index('user_id', 'idx_reviews_user')->whereNull('deleted_at');
            $table->index('rating', 'idx_reviews_rating')->whereNull('deleted_at');

            // Index untuk fitur "Review Terbaru"
            $table->index('created_at', 'idx_reviews_latest')->whereNull('deleted_at');
        });

        // Check Constraint untuk Rating (1-5)
        DB::statement('ALTER TABLE reviews ADD CONSTRAINT reviews_rating_check CHECK (rating >= 1 AND rating <= 5)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
