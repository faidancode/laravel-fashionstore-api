<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CamelCaseToSnakeCase
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isJson()) {
            $request->replace($this->snakeArray($request->all()));
        } else {
            $request->merge($this->snakeArray($request->all()));
        }

        return $next($request);
    }

    private function snakeArray(array $data): array
    {
        $snaked = [];
        foreach ($data as $key => $value) {
            $newKey = is_string($key) ? Str::snake($key) : $key;
            $snaked[$newKey] = is_array($value) ? $this->snakeArray($value) : $value;
        }
        return $snaked;
    }
}