<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tymon\JWTAuth\JWTGuard;

abstract class TestCase extends BaseTestCase
{
    public function actingAsJwt(User $user)
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');
        $token = $guard->login($user);
        return $this->withHeader('Authorization', "Bearer $token");
    }
}
