<?php

declare(strict_types=1);

namespace Sajya\Server\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GzipCompress
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, int $compress = 6)
    {
        $response = $next($request);

        return in_array('gzip', $request->getEncodings(), true)
            ? $this->compress($response, $compress)
            : $response;
    }

    /**
     * Compresses the content of the given response using gzip compression.
     *
     * @param JsonResponse $response
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function compress($response, int $compress)
    {
        $content = gzencode($response->content(), $compress);

        return $response->setContent($content)->withHeaders([
            'Content-Encoding' => 'gzip',
        ]);
    }
}
