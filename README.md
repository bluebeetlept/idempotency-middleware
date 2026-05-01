# Idempotency Middleware

[![Tests][icon-action-tests]][url-action-tests]
[![Code Analysis][icon-action-analysis]][url-action-analysis]
[![License][icon-license]][url-license]

Simple and fully tested Laravel middleware for implementing idempotency in your API requests.

## Installation

```bash
composer require bluebeetle/idempotency-middleware
```

The package will automatically register itself.

## Configuration

You can optionally publish the config file with:

```bash
php artisan vendor:publish --tag="bluebeetle-idempotency-config"
```

This will create a file at `config/bluebeetle/idempotency.php`, where you can adjust the header names, the TTL of the cached response, and the HTTP methods considered idempotent.

Or if you prefer, without publishing the config file, you can define the following on your `.env` file:

```dotenv
IDEMPOTENCY_MAIN_HEADER="X-Idempotency-Key"
IDEMPOTENCY_REPEATED_HEADER="X-Idempotent-Replayed"
IDEMPOTENCY_EXPIRATION=720 # 12 hours (define in minutes)
```

## Usage

To use it, just apply the middleware in your route groups or a single route.

```php
use BlueBeetle\IdempotencyMiddleware\Idempotency;

Route::middleware(['auth:api', Idempotency::class])->group(function () {
    Route::post('/create-user', function () {
        // Create the user ...
    });
});
```

```php
use BlueBeetle\IdempotencyMiddleware\Idempotency;

Route::post('/create-user', function () {
    // Create the user ...
})->middleware(Idempotency::class);
```

### HTTP Requests

To perform an idempotent request, provide an additional `Idempotency-Key` header through the request options where the value should be a **valid UUID v4**.

```http request
POST /api/create-user HTTP/1.1
Content-Type: application/json
Idempotency-Key: 6b3fd36c-24c6-4eb2-a764-bb6c91b33e56

{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "password": "secret"
}
```

> [!NOTE]
> If an idempotency key is reused with the same content body and endpoint, the original response, which is cached, will be returned, instead of continuing the normal execution and the header `Idempotent-Replayed` will be appended to the response.

## Exceptions

The middleware will throw a `BlueBeetle\IdempotencyMiddleware\IdempotencyException` in some scenarios:

- If the idempotency key is not a valid UUID V4
- If the idempotency key was used before but the content body is different
- If the idempotency key was used before but the endpoint is different

## Testing

```bash
composer test
```

## Credits

- [Blue Beetle](https://bluebeetle.pt)
- [All Contributors](../../contributors)

## License

Licensed under the [MIT license](https://opensource.org/licenses/MIT).

[url-action-tests]: https://github.com/bluebeetlept/idempotency-middleware/actions?query=workflow%3Atests
[url-action-analysis]: https://github.com/bluebeetlept/idempotency-middleware/actions?query=workflow%3Acode-analysis
[url-license]: https://opensource.org/licenses/MIT

[icon-action-tests]: https://github.com/bluebeetlept/idempotency-middleware/actions/workflows/tests.yml/badge.svg?branch=main
[icon-action-analysis]: https://github.com/bluebeetlept/idempotency-middleware/actions/workflows/code-analysis.yml/badge.svg?branch=main
[icon-license]: https://img.shields.io/github/license/bluebeetlept/idempotency-middleware?label=License
