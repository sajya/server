<?php

declare(strict_types=1);

namespace Sajya\Server\Http;

use Exception;
use Illuminate\Support\Collection;

class Parser
{
    /**
     * Flag indicating a parse error.
     */
    protected bool $isParseError = false;

    /**
     * Raw content
     */
    protected string $content;

    /**
     * Extract content
     */
    protected ?Collection $decode;

    /**
     * @var bool
     */
    protected bool $batching = false;

    /**
     * @var bool
     */
    protected bool $notification = false;

    /**
     * ContentValidation constructor.
     *
     * @param string $content
     */
    public function __construct(string $content = '')
    {
        $this->content = $content;

        try {
            $decode = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            $this->decode = collect($decode);
            $this->batching = $this->decode->filter(fn($value) => !is_array($value))->isEmpty();
        } catch (Exception $e) {
            $this->isParseError = true;
        }
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->isParseError;
    }

    /**
     * @return array|mixed
     */
    public function getContent()
    {
        return $this->decode;
    }

    /**
     * @return Request[]
     */
    public function makeRequests(): array
    {
        if ($this->isBatch()) {
            return $this->decode
                ->map(fn($options) => Request::loadArray($options))
                ->toArray();
        }

        return [Request::loadArray($this->decode->toArray())];
    }

    /**
     * @return bool
     */
    public function isBatch(): bool
    {
        return $this->batching;
    }
}
