<?php

namespace Tests\Unit\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $cartRepo;
    protected $productRepo;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartRepo = Mockery::mock(CartRepository::class);
        $this->productRepo = Mockery::mock(ProductRepository::class);
        $this->service = new CartService($this->cartRepo, $this->productRepo);
    }

    public function test_add_item_successfully(): void
    {
        $userId = 'user-123';
        $productId = 'product-123';
        $quantity = 2;

        $cart = new Cart(['id' => 'cart-123', 'user_id' => $userId]);
        $product = new Product(['id' => $productId, 'price' => 150000]);

        $this->cartRepo->shouldReceive('createCart')
            ->once()
            ->with($userId)
            ->andReturn($cart);

        $this->productRepo->shouldReceive('getById')
            ->once()
            ->with($productId)
            ->andReturn($product);

        $this->cartRepo->shouldReceive('addItem')
            ->once()
            ->with([
                'cart_id' => 'cart-123',
                'product_id' => $productId,
                'quantity' => $quantity,
                'price_at_add' => 150000,
            ]);

        $this->cartRepo->shouldReceive('getDetail')
            ->once()
            ->with($userId)
            ->andReturn(collect([(object) ['product_id' => $productId]]));

        $result = $this->service->addItem($userId, $productId, $quantity);

        $this->assertCount(1, $result);
    }

    public function test_update_qty_throws_exception_if_item_not_found(): void
    {
        $userId = 'user-123';
        $productId = 'product-123';

        $this->cartRepo->shouldReceive('getByUserId')
            ->once()
            ->with($userId)
            ->andReturn(new Cart(['id' => 'cart-123', 'user_id' => $userId]));

        $this->cartRepo->shouldReceive('updateQty')
            ->once()
            ->with([
                'cart_id' => 'cart-123',
                'product_id' => $productId,
                'quantity' => 3,
            ])
            ->andReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Item tidak ditemukan.');

        $this->service->updateQty($userId, $productId, 3);
    }

    public function test_update_qty_returns_updated_detail(): void
    {
        $userId = 'user-123';
        $productId = 'product-123';

        $this->cartRepo->shouldReceive('getByUserId')
            ->once()
            ->with($userId)
            ->andReturn(new Cart(['id' => 'cart-123', 'user_id' => $userId]));

        $this->cartRepo->shouldReceive('updateQty')
            ->once()
            ->with([
                'cart_id' => 'cart-123',
                'product_id' => $productId,
                'quantity' => 5,
            ])
            ->andReturn(new CartItem(['product_id' => $productId, 'quantity' => 5]));

        $this->cartRepo->shouldReceive('getDetail')
            ->once()
            ->with($userId)
            ->andReturn(collect([(object) ['product_id' => $productId, 'quantity' => 5]]));

        $result = $this->service->updateQty($userId, $productId, 5);

        $this->assertCount(1, $result);
    }
}
