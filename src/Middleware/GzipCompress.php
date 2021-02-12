<?php

declare(strict_types=1);

namespace Sajya\Server\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GzipCompress
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param int     $compress
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, int $compress = 6)
    {
        $response = $next($request);

        return in_array('gzip', $request->getEncodings(), true)
            ? $this->compress($response, $compress)
            : $response;
    }

    /**
     * @param JsonResponse $response
     * @param int          $compress
     *
     * @return Response
     */
    protected function compress($response, int $compress)
    {
        $content = gzencode($response->content(), $compress);

        return $response->setContent($content)->withHeaders([
            'Content-Encoding' => 'gzip',
        ]);
    }
}
