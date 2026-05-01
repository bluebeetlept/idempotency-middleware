# Upgrade Guide

## Upgrading from v1.x to v2.0

v2.0 moves the package from the `eufaturo` organization to `bluebeetle`. This is a namespace and configuration change only -- there are no behavioral changes.

### 1. Update Composer dependency

```diff
- "eufaturo/idempotency-middleware": "^1.0"
+ "bluebeetle/idempotency-middleware": "^2.0"
```

### 2. Update namespace imports

Search and replace across your codebase:

```diff
- use Eufaturo\IdempotencyMiddleware\Idempotency;
+ use BlueBeetle\IdempotencyMiddleware\Idempotency;

- use Eufaturo\IdempotencyMiddleware\IdempotencyException;
+ use BlueBeetle\IdempotencyMiddleware\IdempotencyException;

- use Eufaturo\IdempotencyMiddleware\IdempotencyServiceProvider;
+ use BlueBeetle\IdempotencyMiddleware\IdempotencyServiceProvider;
```

### 3. Update published config file

If you published the config file, move it:

```bash
mv config/eufaturo/idempotency.php config/bluebeetle/idempotency.php
```

If the `config/eufaturo/` directory is now empty, remove it:

```bash
rmdir config/eufaturo
```

### 4. Update config references

If you reference the config directly in your code:

```diff
- config('eufaturo.idempotency.http_methods')
+ config('bluebeetle.idempotency.http_methods')

- config('eufaturo.idempotency.main_header_name')
+ config('bluebeetle.idempotency.main_header_name')

- config('eufaturo.idempotency.repeated_header_name')
+ config('bluebeetle.idempotency.repeated_header_name')

- config('eufaturo.idempotency.expiration_time')
+ config('bluebeetle.idempotency.expiration_time')
```

### 5. Update vendor publish tag (if used in scripts)

```diff
- php artisan vendor:publish --tag="eufaturo-idempotency-config"
+ php artisan vendor:publish --tag="bluebeetle-idempotency-config"
```
