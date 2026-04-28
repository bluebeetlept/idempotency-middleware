# Idempotency Middleware for Laravel

[![GitHub Tests Action Status][icon-action-tests]][url-action-tests]
[![GitHub Code Analysis Action Status][icon-action-analysis]][url-action-analysis]
[![Software License][icon-license]][url-license]
[![Latest Version on Packagist][icon-packagist-version]][url-packagist]
[![Total Downloads][icon-packagist-downloads]][url-packagist]

Simple and fully tested Laravel middleware for implementing idempotency in your API requests.

---

## Installation

```bash
composer require bluebeetlept/idempotency-middleware
```

The package will automatically register itself.

## Configuration

You can optionally publish the config file with:

```sh
php artisan vendor:publish --tag="bluebeetle-idempotency-config"
```

This will create a file at `config/bluebeetle/idempotency.php`, where you can adjust the header names, the TTL of the cached response, and the HTTP methods considered idempotent.

Or if you prefer, without publishing the config file, you can define the following on your `.env` file:

```dotenv
IDEMPOTENCY_MAIN_HEADER="X-Idempotency-Key"
IDEMPOTENCY_REPEATED_HEADER="X-Idempotent-Replayed"
IDEMPOTENCY_EXPIRATION=720 # 12 hours (define in minutes)
```

## How to use

To use it, just apply the middleware in your route groups or a single route.

#### Example 1

```php
<?php

use BlueBeetle\IdempotencyMiddleware\Idempotency;

Route::middleware(['auth:api', Idempotency::class])->group(function () {
    Route::post('/create-user', function () {
        // Create the user ...
    });
});
```

#### Example 2

```php
<?php

use BlueBeetle\IdempotencyMiddleware\Idempotency;

Route::post('/create-user', function () {
    // Create the user ...
})->middleware(Idempotency::class);
```

### Usage with HTTP Requests

To perform an idempotent request, provide an additional `Idempotency-Key` header through the request options where the value should be a **valid UUID v4**.

#### Example

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

**Note:** If an idempotency key is reused with the same content body and endpoint, the original response, which is cached, will be returned, instead of continuing the normal execution and the header `Idempotent-Replayed` will be appended to the response.

## Exceptions / Errors

The middleware will throw a `BlueBeetle\IdempotencyMiddleware\IdempotencyException` in some scenarios:

- If the idempotency key is not a valid UUID V4
- If the idempotency key was used before but the content body is different
- If the idempotency key was used before but the endpoint is different

---

## Testing

```bash
composer test
```

## Contributing

Thank you for your interest. Here are some of the many ways to contribute.

- Check out our [contributing guide](/.github/CONTRIBUTING.md)
- Look at our [code of conduct](/.github/CODE_OF_CONDUCT.md)

## License

This library is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Credits

Built with care by the [BlueBeetle](https://bluebeetle.pt) team.

[url-action-tests]: https://github.com/bluebeetlept/idempotency-middleware/actions?query=workflow%3Atests
[url-action-analysis]: https://github.com/bluebeetlept/idempotency-middleware/actions?query=workflow%3Acode-analysis
[url-packagist]: https://packagist.org/packages/bluebeetlept/idempotency-middleware
[url-license]: https://opensource.org/licenses/MIT

[icon-action-tests]: https://github.com/bluebeetlept/idempotency-middleware/actions/workflows/tests.yml/badge.svg?branch=main
[icon-action-analysis]: https://github.com/bluebeetlept/idempotency-middleware/actions/workflows/code-analysis.yml/badge.svg?branch=main
[icon-license]: https://img.shields.io/github/license/bluebeetlept/idempotency-middleware?label=License
[icon-packagist-version]: https://img.shields.io/packagist/v/bluebeetlept/idempotency-middleware.svg?label=Packagist
[icon-packagist-downloads]: https://img.shields.io/packagist/dt/bluebeetlept/idempotency-middleware.svg?label=Downloads
