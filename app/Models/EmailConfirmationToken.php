<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EmailConfirmationToken extends Model
{
    use HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';
    const UPDATED_AT = null;
    public $timestamps = false; // Karena hanya butuh created_at
    protected $fillable = ['user_id', 'token', 'pin', 'expires_at', 'created_at'];

    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }
}
