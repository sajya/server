<?php

declare(strict_types=1);

namespace Sajya\Server\Http;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Exceptions\InvalidRequestException;
use Sajya\Server\Exceptions\ParseErrorException;

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
            $this->batching = $this->decode
                ->keys()
                ->filter(fn($value) => is_string($value))
                ->isEmpty();

            $this->batching = $this->decode->isEmpty() ? false : $this->batching;

            $emptyIdRequest = $this->decode
                ->when(!$this->batching, fn($request) => collect([$request]))
                ->first(fn($value) => !isset($value['id']));

            $this->notification = $emptyIdRequest !== null;
        } catch (Exception| \TypeError $e) {
            $this->decode = collect();
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
     * @return Collection
     */
    public function getContent(): Collection
    {
        return $this->decode;
    }

    /**
     * @return Request[]|Exception[]
     */
    public function makeRequests(): array
    {
        if ($this->isBatch()) {
            return $this->decode
                ->map(fn($options) => $this->checkValidation($options))
                ->whenEmpty(fn(Collection $collection) => $collection->push($this->checkValidation()))
                ->map(fn($options) => $options instanceof Exception ? $options : Request::loadArray($options))
                ->toArray();
        }

        $options = $this->checkValidation($this->decode->toArray());

        return [is_array($options) ? Request::loadArray($options) : $options];
    }

    /**
     * @return bool
     */
    public function isBatch(): bool
    {
        return $this->batching;
    }

    /**
     * @return bool
     */
    public function isNotification(): bool
    {
        return $this->notification;
    }

    /**
     * @param bool|string|array|int $options
     *
     * @return InvalidParams|ParseErrorException|InvalidRequestException|array
     */
    public function checkValidation($options = [])
    {
        if ($this->isError()) {
            return new ParseErrorException();
        }

        if (!is_array($options) || !$this->isAssociative($options)) {
            return new InvalidRequestException();
        }

        $validation = Validator::make($options, self::rules());

        return $validation->fails()
            ? new InvalidParams($validation->errors()->toArray())
            : $options;
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    private function isAssociative(array $array): bool
    {
        return collect($array)->keys()->filter(fn($key) => is_string($key))->isNotEmpty();
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        return [
            'jsonrpc' => 'required|in:"2.0"',
            'method'  => 'required|string',
            'params'  => 'array',
            'id'      => 'regex:/^\d*(\.\d{2})?$/|nullable',
        ];
    }
}
