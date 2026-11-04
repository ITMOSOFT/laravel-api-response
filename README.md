# Laravel API Response

[![Tests](https://github.com/ITMOSOFT/laravel-api-response/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/ITMOSOFT/laravel-api-response/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/itmosoft/laravel-api-response)](https://packagist.org/packages/itmosoft/laravel-api-response)
[![License](https://img.shields.io/packagist/l/itmosoft/laravel-api-response)](LICENSE)

A small, consistent, configurable JSON response layer for Laravel applications. It provides a predictable response envelope, validation errors, pagination metadata, and a facade without replacing Laravel's exception handling or resource system.

## Requirements

- PHP 8.3+
- Laravel 12 or 13

## Installation

```bash
composer require itmosoft/laravel-api-response
```

Laravel package discovery registers the service provider and facade alias automatically.

Publish the configuration if you want to customize defaults:

```bash
php artisan vendor:publish --tag=api-response-config
```

## Basic usage

```php
use ItmoSoft\ApiResponse\Facades\ApiResponse;

return ApiResponse::success($user, 'User loaded.');

return ApiResponse::created($user);

return ApiResponse::error(
    message: 'User not found.',
    status: 404,
    code: 'USER_NOT_FOUND',
);

return ApiResponse::validation($validator->errors()->toArray());

return ApiResponse::noContent();
```

The facade can also be replaced with dependency injection:

```php
use ItmoSoft\ApiResponse\ApiResponseManager;

public function show(int $id, ApiResponseManager $apiResponse)
{
    $user = User::findOrFail($id);

    return $apiResponse->success($user);
}
```

## Response envelope

Successful response:

```json
{
  "success": true,
  "message": "User loaded.",
  "data": { "id": 7, "name": "Adeel" },
  "errors": null,
  "meta": {}
}
```

Error response:

```json
{
  "success": false,
  "message": "User not found.",
  "data": null,
  "errors": null,
  "meta": { "code": "USER_NOT_FOUND" }
}
```

The `meta` field is always an object in JSON, including when it is empty. Fields can be disabled individually in configuration.

## Pagination

Pass a Laravel paginator instance:

```php
$users = User::query()->paginate(15);

return ApiResponse::paginated($users, 'Users loaded.');
```

The response includes paginator items in `data` and pagination details in `meta.pagination` (current page, per-page size, item range, path, more-pages flag, and total/last page for length-aware pagination). Navigation URLs are included by default and can be disabled.

## Methods

| Method | Purpose | Default status |
| --- | --- | ---: |
| `success($data, $message, $status, $meta, $headers)` | Successful JSON response | 200 |
| `created($data, $message, $meta, $headers)` | Resource created | 201 |
| `error($message, $errors, $status, $code, $data, $meta, $headers)` | Error JSON response | 400 |
| `validation($errors, $message, $headers)` | Validation failure | 422 |
| `paginated($paginator, $message, $meta, $headers)` | Paginated data | 200 |
| `noContent($headers)` | Empty response | 204 |

`error()` accepts only HTTP status codes from 400 through 599. Application error codes are placed in `meta.code`.

## Configuration

`config/api-response.php` supports:

- Default messages for success, error, created, and validation responses.
- Enabling or disabling envelope fields (`success`, `message`, `data`, `errors`, `meta`).
- Enabling or disabling pagination navigation links.

## Laravel resources and serialization

The `data` argument is passed to Laravel's JSON response factory. Arrays, Eloquent models, `JsonSerializable` values, and Laravel resources that implement Laravel's response/serialization contracts can be supplied. For paginated responses, the paginator's `items()` are placed in `data`; if you need resource transformation, transform the paginator collection before passing it in.

## Exception handling

This package deliberately does not override Laravel's global exception handler. Use `ApiResponse::error()` or `ApiResponse::validation()` where you want the standardized envelope, or integrate those methods into your application's exception configuration. This avoids surprising changes to web routes and framework exception behavior.

## Testing

```bash
composer install
composer test
```

The GitHub Actions matrix tests Laravel 12 with Testbench 10 and Laravel 13 with Testbench 11 on PHP 8.3.

## First release and verification

See [RELEASE.md](RELEASE.md) for the complete first-release procedure, including local Laravel 12/13 test commands, Git commit and push commands, the annotated `v1.0.0` tag, GitHub Actions verification, Packagist registration, and installation smoke tests in Laravel applications.

## License

The MIT License. See [LICENSE](LICENSE).
