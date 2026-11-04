<?php

namespace ItmoSoft\ApiResponse\Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use ItmoSoft\ApiResponse\ApiResponseManager;
use ItmoSoft\ApiResponse\Facades\ApiResponse;
use ItmoSoft\ApiResponse\Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_success_response_has_the_standard_envelope(): void
    {
        $response = ApiResponse::success(['id' => 7], 'Loaded');

        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame([
            'success' => true,
            'message' => 'Loaded',
            'data' => ['id' => 7],
            'errors' => null,
            'meta' => [],
        ], $response->getData(true));
    }

    public function test_success_response_uses_configured_default_message(): void
    {
        config(['api-response.messages.success' => 'All good']);

        $this->assertSame('All good', ApiResponse::success()->getData(true)['message']);
    }

    public function test_success_response_supports_custom_status_meta_and_headers(): void
    {
        $response = ApiResponse::success(['ok' => true], status: 202, meta: ['trace_id' => 'abc'], headers: ['X-Trace' => 'abc']);

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('abc', $response->headers->get('X-Trace'));
        $this->assertSame('abc', $response->getData(true)['meta']['trace_id']);
    }

    public function test_created_response_uses_http_201(): void
    {
        $response = ApiResponse::created(['id' => 10]);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertTrue($response->getData(true)['success']);
    }

    public function test_error_response_contains_errors_and_application_code(): void
    {
        $response = ApiResponse::error('Not found', ['resource' => ['Missing']], 404, 'RESOURCE_NOT_FOUND');

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('RESOURCE_NOT_FOUND', $response->getData(true)['meta']['code']);
        $this->assertSame(['resource' => ['Missing']], $response->getData(true)['errors']);
        $this->assertFalse($response->getData(true)['success']);
    }

    public function test_error_rejects_non_error_http_status_codes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ApiResponse::error(status: 200);
    }

    public function test_validation_response_uses_422_and_validation_code(): void
    {
        $response = ApiResponse::validation(['email' => ['The email field is required.']]);
        $payload = $response->getData(true);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('VALIDATION_ERROR', $payload['meta']['code']);
        $this->assertArrayHasKey('email', $payload['errors']);
    }

    public function test_paginated_response_contains_items_and_pagination_metadata(): void
    {
        $paginator = new LengthAwarePaginator(
            items: new Collection([['id' => 1], ['id' => 2]]),
            total: 5,
            perPage: 2,
            currentPage: 2,
            options: ['path' => 'https://example.test/api/items'],
        );

        $payload = ApiResponse::paginated($paginator)->getData(true);

        $this->assertSame([['id' => 1], ['id' => 2]], $payload['data']);
        $this->assertSame(2, $payload['meta']['pagination']['current_page']);
        $this->assertSame(5, $payload['meta']['pagination']['total']);
        $this->assertSame(3, $payload['meta']['pagination']['last_page']);
        $this->assertNotNull($payload['meta']['pagination']['links']['prev']);
    }

    public function test_pagination_links_can_be_disabled(): void
    {
        config(['api-response.pagination.include_links' => false]);
        $paginator = new LengthAwarePaginator([['id' => 1]], 1, 10, 1);

        $payload = app(ApiResponseManager::class)->paginated($paginator)->getData(true);

        $this->assertArrayNotHasKey('links', $payload['meta']['pagination']);
    }

    public function test_response_fields_can_be_omitted_through_configuration(): void
    {
        config(['api-response.fields.errors' => false, 'api-response.fields.meta' => false]);

        $payload = ApiResponse::success(['id' => 1])->getData(true);

        $this->assertArrayNotHasKey('errors', $payload);
        $this->assertArrayNotHasKey('meta', $payload);
    }

    public function test_empty_meta_is_encoded_as_an_object(): void
    {
        $payload = json_decode(ApiResponse::success()->getContent());

        $this->assertIsObject($payload->meta);
    }

    public function test_no_content_response_returns_http_204_without_body(): void
    {
        $response = ApiResponse::noContent();

        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }

    public function test_manager_is_registered_as_a_singleton(): void
    {
        $this->assertSame(app(ApiResponseManager::class), app(ApiResponseManager::class));
        $this->assertSame(app(ApiResponseManager::class), app('api-response'));
    }

    public function test_config_can_be_published(): void
    {
        $this->artisan('vendor:publish', ['--tag' => 'api-response-config', '--force' => true])
            ->assertExitCode(0);

        $this->assertFileExists(config_path('api-response.php'));
    }
}
