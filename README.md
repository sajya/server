## Introduction

[![Tests](https://github.com/sajya/server/actions/workflows/phpunit.yml/badge.svg)](https://github.com/sajya/server/actions/workflows/phpunit.yml)
[![Quality Assurance](https://github.com/sajya/server/actions/workflows/quality.yml/badge.svg)](https://github.com/sajya/server/actions/workflows/quality.yml)
[![Coding Guidelines](https://github.com/sajya/server/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/sajya/server/actions/workflows/php-cs-fixer.yml)
[![codecov](https://codecov.io/gh/sajya/server/branch/master/graph/badge.svg)](https://codecov.io/gh/sajya/server)
[![Total Downloads](https://img.shields.io/packagist/dt/sajya/server.svg)](https://packagist.org/packages/sajya/server)

Sajya empowers Laravel developers to bring the full power of JSON-RPC 2.0 to life. With Sajya, you can:

* **Achieve Complete JSON-RPC 2.0 Compliance:** Seamlessly implement every feature of the specification.
* **Ensure Robust Validation:** Automatically validate parameters to keep your interactions safe and sound.
* **Handle Batch & Notification Requests:** Easily manage multiple requests or fire-and-forget notifications.
* **Set Up in a Snap:** Get your JSON-RPC server up and running with minimal fuss.

It’s all about making your life easier so you can focus on building amazing applications.

## Official Documentation

Discover all there is to know about Sajya on our [website](https://sajya.github.io/). You'll find everything from
installation guides to deep dives into each feature.

## Defining a procedure

Use the `Procedure` attribute to give a class its RPC name:

```php
use Sajya\Server\Attributes\Procedure;

#[Procedure('tennis')]
class TennisProcedure
{
    public function ping(): string
    {
        return 'pong';
    }
}
```

Register `TennisProcedure::class` with your RPC route and call `tennis@ping`.
When migrating, replace `use Sajya\Server\Procedure` with the attribute import,
remove `extends Procedure` and `public static string $name`, and add
`#[Procedure('your-name')]` to each procedure class.

## Testing RPC methods

Use `MakesRpcRequests` in your test class or base `TestCase`. Configure the endpoint
once in `setUp()`, then call RPC methods with `rpc()`:

```php
use Sajya\Server\Testing\MakesRpcRequests;
use Tests\TestCase;

class TennisTest extends TestCase
{
    use MakesRpcRequests;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withRpcEndpoint(route('rpc.endpoint'));
    }

    public function test_ping(): void
    {
        $this->rpc('tennis@ping')
            ->assertOk()
            ->assertJsonPath('result', 'pong')
            ->assertJsonMissingPath('error');
    }
}
```

`withRpcEndpoint()` accepts a URL or path. Use Laravel's `route()` helper for a
named route. `rpc()` accepts the method name, a parameter array, and an optional
request ID, which defaults to `1`:

```php
$this->rpc('math@subtract', ['a' => 42, 'b' => 23], id: 'subtract-1')
    ->assertOk()
    ->assertJsonPath('result', 19);
```

`rpc()` returns Laravel's `TestResponse` without automatic assertions. Use
`postJson()` for notifications, batches, or malformed requests.

`ProceduralRequests` is deprecated and remains available for existing tests.
Replace `setRpcRoute($name)` with `withRpcEndpoint(route($name))`, `setRpcUrl($url)`
with `withRpcEndpoint($url)`, and either request method with `rpc()`.
When replacing `callProcedure()`, add its previous checks explicitly with
`assertOk()->assertHeader('content-type', 'application/json')`.
Its public methods carry the native `Deprecated` attribute, which emits warnings
on PHP 8.4 and later. The trait also has a `@deprecated` tag for IDEs.

## Changelog

Curious about what’s new? Check out our [CHANGELOG](CHANGELOG.md) to keep up with the latest updates and improvements.

## Contributing

We’re all about community! Whether you have a bug fix, a new feature idea, or an improvement for our docs, we’d love to
hear from you.

## Maintainers

Sajya is proudly maintained by [Alexandr Chernyaev](https://github.com/tabuna). Feel free to reach out or get involved!

## License

Sajya is released under the MIT License. See the [License File](LICENSE.md) for more details.
