<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Illuminate\Support\Collection;
use Sajya\Server\Tests\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ExpectedTest extends TestCase
{
    private const REQUEST_PATH = __DIR__ . '/../Expected/Requests';
    private const RESPONSE_PATH = __DIR__ . '/../Expected/Responses';


    public function testExpectedRequestResponse(): void
    {
        $this->getRequestResponseContent()->each(function (array $params, string $key) {

            $response = $this
                ->call('POST', route('rpc.point'), [], [], [], [], $params['request'])
                ->getContent();

            $this->assertJson($response, 'Error:: ' . $key);
            $this->assertJsonStringEqualsJsonString($params['response'], $response, 'Error:: ' . $key);
        });
    }


    private function getRequestResponseContent(): Collection
    {
        $requests = collect((new Finder())->in(self::REQUEST_PATH)->files())
            ->transform(fn(SplFileInfo $info) => $info->getContents());

        $responses = collect((new Finder())->in(self::RESPONSE_PATH)->files())
            ->transform(fn(SplFileInfo $info) => $info->getContents());

        return $requests->map(static function (string $request, string $key) use ($responses) {
            $keyResponse = str_replace(self::REQUEST_PATH, self::RESPONSE_PATH, $key,);

            $response = $responses->get($keyResponse);

            return compact('request', 'response');
        });
    }
}
