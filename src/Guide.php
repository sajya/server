<?php

declare(strict_types=1);

namespace Sajya\Server;

use Illuminate\Support\Collection;
use Sajya\Server\Http\Request;

class Guide
{
    /**
     * Stores all available RPC commands
     *
     * @var Collection
     */
    protected Collection $map;

    /**
     * Guide constructor.
     *
     * @param array $procedures
     */
    public function __construct(array $procedures = [])
    {
        $this->map = collect($procedures);
    }

    /**
     * @param Request $request
     *
     * @return null|Procedure
     */
    public function findProcedure(Request $request): ?Procedure
    {
        return $this->map
            ->map(fn($procedure) => !is_object($procedure) ? app()->make($procedure) : $procedure)
            ->filter(fn(Procedure $procedure) => $procedure::$name === $request->getMethod())
            ->first();
    }
}
