<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Illuminate\Support\Facades\Route;
use Sajya\Server\Docs;
use Sajya\Server\Tests\TestCase;

class DocsTest extends TestCase
{
    public function testDocs(): void
    {
        $route = Route::getRoutes()->getByName('rpc.docs');

        $docs = new Docs($route);
        $annotations = $docs->getAnnotations();

        $this->assertCount(2, $annotations);

        $ping = $annotations->first();
        $empty = $annotations->last();

        $this->assertEquals('docs', $ping['name']);
        $this->assertEquals('docs', $empty['name']);

        $this->assertEquals('@', $ping['delimiter']);
        $this->assertEquals('@', $ping['delimiter']);

        $this->assertEquals('Execute the procedure.', $ping['description']);
        $this->assertEquals('', $empty['description']);

        $this->assertEquals('ping', $ping['method']);
        $this->assertEquals('empty', $empty['method']);

        $this->assertStringContainsString('required', (string) $ping['request']);
        $this->assertStringContainsString('max:5', (string) $ping['request']);
        $this->assertStringContainsString('key', (string) $ping['request']);
        $this->assertStringContainsString('array', (string) $ping['request']);

        $this->assertNotEmpty((string) $empty['request']);
        $this->assertNotEmpty((string) $empty['response']);
    }
}
