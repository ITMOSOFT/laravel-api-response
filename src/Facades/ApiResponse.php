<?php

namespace ItmoSoft\ApiResponse\Facades;

use Illuminate\Support\Facades\Facade;
use ItmoSoft\ApiResponse\ApiResponseManager;

/**
 * @method static \Illuminate\Http\JsonResponse success(mixed $data = null, ?string $message = null, int $status = 200, array $meta = [], array $headers = [])
 * @method static \Illuminate\Http\JsonResponse created(mixed $data = null, ?string $message = null, array $meta = [], array $headers = [])
 * @method static \Illuminate\Http\JsonResponse error(?string $message = null, mixed $errors = null, int $status = 400, ?string $code = null, mixed $data = null, array $meta = [], array $headers = [])
 * @method static \Illuminate\Http\JsonResponse validation(array $errors, ?string $message = null, array $headers = [])
 * @method static \Illuminate\Http\JsonResponse paginated(\Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator $paginator, ?string $message = null, array $meta = [], array $headers = [])
 * @method static \Illuminate\Http\Response noContent(array $headers = [])
 */
class ApiResponse extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ApiResponseManager::class;
    }
}
