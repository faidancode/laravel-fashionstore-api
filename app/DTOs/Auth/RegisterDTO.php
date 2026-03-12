<?php

// app/DTOs/Auth/RegisterDTO.php
namespace App\DTOs\Auth;

class RegisterDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $name,
        public readonly string $password,
        public readonly ?string $phone = null,
        public readonly string $role = 'CUSTOMER'
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            email: $request->email,
            name: $request->name,
            password: $request->password,
            phone: $request->phone,
            role: $request->role ?? 'CUSTOMER'
        );
    }
}
