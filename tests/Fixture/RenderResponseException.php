<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Fixture;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RenderResponseException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Record not found.',
            'locale'  => $request->getLocale(),
        ]);
    }
}
