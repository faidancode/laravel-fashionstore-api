<?php
// app/Enums/GlobalErrorCode.php
namespace App\Enums;
use App\Contracts\AppErrorInterface;

enum GlobalErrorCode: string implements AppErrorInterface
{
    case INTERNAL_ERROR = 'INTERNAL_ERROR';
    case UNAUTHORIZED = 'UNAUTHORIZED';
    case INVALID_UUID = 'INVALID_ADDRESS_UUID';
    case NOT_FOUND = 'ADDRESS_NOT_FOUND';

    public function code(): string
    {
        return $this->value;
    }
    public function status(): int
    {
        return match ($this) {
            self::INTERNAL_ERROR => 500,
            self::UNAUTHORIZED => 401,
            self::INVALID_UUID => 400,
            self::NOT_FOUND => 404,
        };
    }
    public function message(): string
    {
        return match ($this) {
            self::INTERNAL_ERROR => 'Terjadi kesalahan pada server.',
            self::UNAUTHORIZED => 'Anda tidak memiliki akses.',
            self::INVALID_UUID => 'ID tidak valid.',
            self::NOT_FOUND => 'Data tidak ditemukan.',
        };
    }
}