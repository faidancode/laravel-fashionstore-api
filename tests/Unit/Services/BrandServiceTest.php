<?php

namespace Tests\Unit\Services;

use App\Models\Brand;
use App\Repositories\BrandRepository;
use App\Services\BrandService;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class BrandServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $brandRepo;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->brandRepo = Mockery::mock(BrandRepository::class);
        $this->service = new BrandService($this->brandRepo);
    }

    /**
     * SKENARIO POSITIF
     */
    public function test_create_brand_successfully(): void
    {
        $data = [
            'name' => 'Nike',
            'slug' => 'nike',
            'description' => 'Brand olahraga',
            'image_url' => 'https://example.test/nike.png',
            'is_active' => true,
        ];

        $expectedBrand = new Brand($data);

        $this->brandRepo->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expectedBrand);

        $result = $this->service->createBrand($data);

        $this->assertEquals('Nike', $result->name);
    }

    /**
     * SKENARIO NEGATIF
     */
    public function test_get_brand_detail_throws_exception_if_not_found(): void
    {
        $invalidId = 'brand-999';

        $this->brandRepo->shouldReceive('getById')
            ->once()
            ->with($invalidId)
            ->andReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Brand tidak ditemukan.');

        $this->service->getBrandDetail($invalidId);
    }

    public function test_update_brand_returns_updated_model(): void
    {
        $brandId = 'brand-123';
        $data = [
            'name' => 'Adidas',
            'slug' => 'adidas',
        ];

        $existingBrand = new Brand(['id' => $brandId, 'name' => 'Old', 'slug' => 'old']);
        $updatedBrand = new Brand(array_merge(['id' => $brandId], $data));

        $this->brandRepo->shouldReceive('getById')
            ->once()
            ->with($brandId)
            ->andReturn($existingBrand);

        $this->brandRepo->shouldReceive('update')
            ->once()
            ->with($brandId, $data)
            ->andReturn($updatedBrand);

        $result = $this->service->updateBrand($brandId, $data);

        $this->assertInstanceOf(Brand::class, $result);
        $this->assertEquals('Adidas', $result->name);
    }
}
