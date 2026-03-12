<?php

namespace App\Contracts;

interface AppErrorInterface
{
    public function code(): string;
    public function message(): string;
    public function status(): int;
}