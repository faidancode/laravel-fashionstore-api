<?php

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Services\CategoryService;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $categoryRepo;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryRepo = Mockery::mock(CategoryRepository::class);
        $this->service = new CategoryService($this->categoryRepo);
    }

    /**
     * SKENARIO POSITIF
     */
    public function test_create_category_successfully(): void
    {
        $data = [
            'name' => 'Sneakers',
            'slug' => 'sneakers',
            'description' => 'Kategori sepatu',
            'image_url' => 'https://example.test/sneakers.png',
            'is_active' => true,
        ];

        $expectedCategory = new Category($data);

        $this->categoryRepo->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expectedCategory);

        $result = $this->service->createCategory($data);

        $this->assertEquals('Sneakers', $result->name);
    }

    /**
     * SKENARIO NEGATIF
     */
    public function test_get_category_detail_throws_exception_if_not_found(): void
    {
        $invalidId = 'category-999';

        $this->categoryRepo->shouldReceive('getById')
            ->once()
            ->with($invalidId)
            ->andReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Kategori tidak ditemukan.');

        $this->service->getCategoryDetail($invalidId);
    }

    public function test_update_category_returns_updated_model(): void
    {
        $categoryId = 'category-123';
        $data = [
            'name' => 'Tops',
            'slug' => 'tops',
        ];

        $existingCategory = new Category(['id' => $categoryId, 'name' => 'Old', 'slug' => 'old']);
        $updatedCategory = new Category(array_merge(['id' => $categoryId], $data));

        $this->categoryRepo->shouldReceive('getById')
            ->once()
            ->with($categoryId)
            ->andReturn($existingCategory);

        $this->categoryRepo->shouldReceive('update')
            ->once()
            ->with($categoryId, $data)
            ->andReturn($updatedCategory);

        $result = $this->service->updateCategory($categoryId, $data);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertEquals('Tops', $result->name);
    }
}
