<?php

namespace ItmoSoft\ApiResponse;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use InvalidArgumentException;

class ApiResponseManager
{
    public function __construct(
        protected ResponseFactory $response,
        protected ConfigRepository $config,
    ) {
    }

    public function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200,
        array $meta = [],
        array $headers = [],
    ): JsonResponse {
        return $this->make(
            success: true,
            message: $message ?? $this->message('success', 'Request completed successfully.'),
            data: $data,
            errors: null,
            meta: $meta,
            status: $status,
            headers: $headers,
        );
    }

    public function created(
        mixed $data = null,
        ?string $message = null,
        array $meta = [],
        array $headers = [],
    ): JsonResponse {
        return $this->success(
            data: $data,
            message: $message ?? $this->message('created', 'Resource created successfully.'),
            status: 201,
            meta: $meta,
            headers: $headers,
        );
    }

    public function error(
        ?string $message = null,
        mixed $errors = null,
        int $status = 400,
        ?string $code = null,
        mixed $data = null,
        array $meta = [],
        array $headers = [],
    ): JsonResponse {
        if ($status < 400 || $status > 599) {
            throw new InvalidArgumentException('Error responses must use an HTTP status code between 400 and 599.');
        }

        if ($code !== null) {
            $meta = [...$meta, 'code' => $code];
        }

        return $this->make(
            success: false,
            message: $message ?? $this->message('error', 'The request could not be completed.'),
            data: $data,
            errors: $errors,
            meta: $meta,
            status: $status,
            headers: $headers,
        );
    }

    public function validation(
        array $errors,
        ?string $message = null,
        array $headers = [],
    ): JsonResponse {
        return $this->error(
            message: $message ?? $this->message('validation', 'The given data was invalid.'),
            errors: $errors,
            status: 422,
            code: 'VALIDATION_ERROR',
            headers: $headers,
        );
    }

    public function paginated(
        LengthAwarePaginator|Paginator $paginator,
        ?string $message = null,
        array $meta = [],
        array $headers = [],
    ): JsonResponse {
        $pagination = [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'path' => $paginator->path(),
        ];

        if ($paginator instanceof LengthAwarePaginator) {
            $pagination['total'] = $paginator->total();
            $pagination['last_page'] = $paginator->lastPage();
        }

        $pagination['has_more_pages'] = $paginator->hasMorePages();

        if ($this->config('pagination.include_links', true)) {
            $pagination['links'] = [
                'first' => $paginator instanceof LengthAwarePaginator ? $paginator->url(1) : null,
                'last' => $paginator instanceof LengthAwarePaginator ? $paginator->url($paginator->lastPage()) : null,
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ];
        }

        $meta = ['pagination' => $pagination, ...$meta];

        return $this->success(
            data: $paginator->items(),
            message: $message,
            meta: $meta,
            headers: $headers,
        );
    }

    public function noContent(array $headers = []): Response
    {
        return $this->response->noContent(204, $headers);
    }

    protected function make(
        bool $success,
        string $message,
        mixed $data,
        mixed $errors,
        array $meta,
        int $status,
        array $headers,
    ): JsonResponse {
        $payload = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
            'meta' => (object) $meta,
        ];

        foreach ($payload as $field => $value) {
            if (! $this->config("fields.{$field}", true)) {
                unset($payload[$field]);
            }
        }

        return $this->response->json($payload, $status, $headers);
    }

    protected function message(string $key, string $fallback): string
    {
        return (string) $this->config("messages.{$key}", $fallback);
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        return $this->config->get("api-response.{$key}", $default);
    }
}
