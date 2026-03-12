<?php

namespace Tests\Unit;

use App\DTOs\Auth\RegisterDTO;
use App\Models\User;
use App\Repositories\AuthRepository;
use App\Services\AuthService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Resend\Laravel\Facades\Resend as FacadesResend;
use Tests\TestCase;

class AuthServiceEmailTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_register_sends_confirmation_email_and_creates_token(): void
    {
        $repo = Mockery::mock(AuthRepository::class);

        $repo->shouldReceive('getByEmail')
            ->once()
            ->with('user12@yopmail.com')
            ->andReturn(null);

        $repo->shouldReceive('checkPhoneExists')
            ->once()
            ->with('081234567890')
            ->andReturn(false);

        $user = new User();
        $user->id = 'user-id-1'; // Pastikan ini terisi
        $user->name = 'User Test';
        $user->email = 'user12@yopmail.com';

        $repo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                return $payload['email'] === 'user12@yopmail.com'
                    && $payload['name'] === 'User Test'
                    && $payload['phone'] === '081234567890'
                    && $payload['is_active'] === true
                    && is_string($payload['password']);
            }))
            ->andReturn($user);

        $repo->shouldReceive('upsertEmailConfirmationToken')
            ->once()
            ->with(Mockery::on(function (array $payload) use ($user): bool {
                return $payload['user_id'] === $user->id
                    && is_string($payload['token'])
                    && is_string($payload['pin']);
            }));

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function (callable $callback) {
                return $callback();
            });

        FacadesResend::shouldReceive('emails->send')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                return $payload['to'] === ['user12@yopmail.com']
                    && $payload['subject'] === 'Konfirmasi Email'
                    && is_string($payload['html'])
                    && $payload['html'] !== '';
            }))
            ->andReturn((object) ['id' => 'email_1']);

        $service = new AuthService($repo);
        $dto = new RegisterDTO(
            email: 'user12@yopmail.com',
            name: 'User Test',
            password: 'password123',
            phone: '081234567890'
        );

        $result = $service->register($dto);

        $this->assertSame('user12@yopmail.com', $result->email);
    }

    public function test_request_password_reset_sends_email_and_creates_token(): void
    {
        $repo = Mockery::mock(AuthRepository::class);

        $user = new User([
            'id' => 'user-id-2',
            'name' => 'Reset User',
            'email' => 'reset@example.com',
        ]);

        $repo->shouldReceive('getByEmail')
            ->once()
            ->with('reset@example.com')
            ->andReturn($user);

        $repo->shouldReceive('upsertPasswordResetToken')
            ->once()
            ->with(Mockery::on(function (array $payload) use ($user): bool {
                return $payload['user_id'] === $user->id
                    && is_string($payload['token']);
            }));

        FacadesResend::shouldReceive('emails->send')
            ->once()
            ->with(Mockery::on(function (array $payload) use ($user): bool {
                return $payload['to'] === [$user->email]
                    && $payload['subject'] === 'Reset Password'
                    && is_string($payload['html'])
                    && $payload['html'] !== '';
            }))
            ->andReturn((object) ['id' => 'email_2']);

        $service = new AuthService($repo);
        $service->requestPasswordReset('reset@example.com');

        $this->assertTrue(true);
    }
}
