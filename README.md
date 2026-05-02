# Idempotency Middleware

[![Tests][icon-action-tests]][url-action-tests]
[![Code Analysis][icon-action-analysis]][url-action-analysis]
[![License][icon-license]][url-license]

Simple and fully tested Laravel middleware for implementing idempotency in your API requests.

## Quick Start

```bash
composer require bluebeetle/idempotency-middleware
```

Add the middleware to your routes:

```php
use BlueBeetle\IdempotencyMiddleware\Idempotency;

Route::middleware([Idempotency::class])->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
});
```

Send requests with an `Idempotency-Key` header (UUID v4):

```
POST /api/orders HTTP/1.1
Idempotency-Key: 6b3fd36c-24c6-4eb2-a764-bb6c91b33e56
Content-Type: application/json

{"product_id": 1, "quantity": 2}
```

Repeated requests with the same key return the cached response instead of processing again.

## Documentation

Full documentation is available at [bluebeetle.pt/open-source/docs/idempotency-middleware/v1](https://bluebeetle.pt/open-source/docs/idempotency-middleware/v1).

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
