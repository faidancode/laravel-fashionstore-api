<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\ProductService;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $productRepo;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepo = Mockery::mock(ProductRepository::class);
        $this->service = new ProductService($this->productRepo);
    }

    /**
     * SKENARIO POSITIF
     */
    public function test_create_product_successfully(): void
    {
        $data = [
            'category_id' => 'cat-123',
            'brand_id' => 'brand-123',
            'name' => 'Product A',
            'slug' => 'product-a',
            'description' => 'Deskripsi produk',
            'price' => 199000,
            'stock' => 10,
            'sku' => 'SKU-1234',
            'image_url' => 'https://example.test/product-a.png',
            'is_active' => true,
        ];

        $expectedProduct = new Product($data);

        $this->productRepo->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expectedProduct);

        $result = $this->service->createProduct($data);

        $this->assertEquals('Product A', $result->name);
    }

    /**
     * SKENARIO NEGATIF
     */
    public function test_get_product_detail_throws_exception_if_not_found(): void
    {
        $invalidId = 'product-999';

        $this->productRepo->shouldReceive('getById')
            ->once()
            ->with($invalidId)
            ->andReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Produk tidak ditemukan.');

        $this->service->getProductDetail($invalidId);
    }

    public function test_update_product_returns_updated_model(): void
    {
        $productId = 'product-123';
        $data = [
            'name' => 'Product B',
            'slug' => 'product-b',
        ];

        $existingProduct = new Product(['id' => $productId, 'name' => 'Old', 'slug' => 'old']);
        $updatedProduct = new Product(array_merge(['id' => $productId], $data));

        $this->productRepo->shouldReceive('getById')
            ->once()
            ->with($productId)
            ->andReturn($existingProduct);

        $this->productRepo->shouldReceive('update')
            ->once()
            ->with(array_merge(['id' => $productId], $data))
            ->andReturn($updatedProduct);

        $result = $this->service->updateProduct($productId, $data);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals('Product B', $result->name);
    }
}
