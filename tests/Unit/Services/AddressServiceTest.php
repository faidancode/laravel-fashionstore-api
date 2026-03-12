<?php
namespace Tests\Unit\Services;

use App\Models\Address;
use App\Repositories\AddressRepository;
use App\Services\AddressService;
use Exception;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class AddressServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $addressRepo;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->addressRepo = Mockery::mock(AddressRepository::class);
        $this->service = new AddressService($this->addressRepo);
    }

    /**
     * SKENARIO POSITIF
     */
    public function test_create_address_sets_primary_correctly(): void
    {
        $userId = 'user-123';
        $data = [
            'user_id' => $userId,
            'label' => 'Rumah',
            'is_primary' => true
        ];

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $this->addressRepo->shouldReceive('unsetPrimaryByUser')
            ->once()
            ->with($userId);

        // Gunakan Mockery::on untuk melihat isi payload jika masih error
        $expectedAddress = new Address($data);

        $this->addressRepo->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expectedAddress);

        $result = $this->service->createAddress($data);

        // Sekarang $result adalah instance dari App\Models\Address
        $this->assertEquals('Rumah', $result->label);
    }

    /**
     * SKENARIO NEGATIF
     */
    public function test_get_address_detail_throws_exception_if_not_found(): void
    {
        $invalidId = 'addr-999';

        // Mock Repository return null
        $this->addressRepo->shouldReceive('getById')
            ->once()
            ->with($invalidId)
            ->andReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Alamat tidak ditemukan.");

        $this->service->getAddressDetail($invalidId);
    }

    public function test_set_as_primary_executes_correctly(): void
    {
        $userId = 'user-123';
        $addressId = 'addr-456';

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $this->addressRepo->shouldReceive('unsetPrimaryByUser')
            ->once()
            ->with($userId);

        // BUAT INSTANCE MODEL:
        $expectedAddress = new Address([
            'id' => $addressId,
            'is_primary' => true
        ]);

        $this->addressRepo->shouldReceive('update')
            ->once()
            ->with($addressId, ['is_primary' => true])
            ->andReturn($expectedAddress); // GANTI: dari true menjadi objek model

        $result = $this->service->setAsPrimary($userId, $addressId);

        // ASSERT: pastikan hasilnya adalah instance Address dan is_primary bernilai true
        $this->assertInstanceOf(Address::class, $result);
        $this->assertTrue($result->is_primary);
    }
}