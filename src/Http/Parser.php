<?php

declare(strict_types=1);

namespace Sajya\Server\Http;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Exceptions\InvalidRequestException;
use Sajya\Server\Exceptions\ParseErrorException;
use Sajya\Server\Rules\Identifier;
use TypeError;

class Parser
{
    /**
     * Flag indicating a parse error.
     */
    protected bool $isParseError = false;

    /**
     * Raw content.
     */
    protected string $content;

    /**
     * Extract content.
     */
    protected Collection $decode;

    /**
     * Indicates whether the content is a batch request.
     *
     * @var bool
     */
    protected bool $batching = false;

    /**
     * Indicates whether the content is a notification request.
     *
     * @var bool
     */
    protected bool $notification = false;

    /**
     * Parser constructor.
     *
     * @param string $content
     */
    public function __construct(string $content = '')
    {
        $this->content = $content;

        try {
            $decode = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            $this->decode = collect($decode);
            $this->batching = $this->decode->isNotEmpty() && Arr::isList($this->decode->toArray());

            $emptyIdRequest = $this->decode
                ->when(! $this->batching, fn ($request) => collect([$request]))
                ->first(fn ($value) => ! isset($value['id']));

            $this->notification = $emptyIdRequest !== null;
        } catch (Exception|TypeError $e) {
            $this->decode = collect();
            $this->isParseError = true;
        }
    }

    /**
     * Check if the response is an error.
     *
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
        $content = $this->getContent();

        if ($this->isBatch()) {
            return $content
                ->map(fn ($options) => $this->checkValidation($options))
                ->whenEmpty(fn (Collection $collection) => $collection->push($this->checkValidation()))
                ->map(fn ($options) => $options instanceof Exception ? $options : Request::loadArray($options))
                ->toArray();
        }

        $options = $this->checkValidation($content->toArray());

        return [is_array($options) ? Request::loadArray($options) : $options];
    }

    /**
     * Determine whether the current request is a batch request.
     *
     * @return bool
     */
    public function isBatch(): bool
    {
        return $this->batching;
    }

    /**
     * Determine whether the current request is a notification request.
     *
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

        if (! is_array($options) || Arr::isList($options)) {
            return new InvalidRequestException();
        }

        $data = $options;

        // skip deep parameters for validator
        if (isset($options['params']) && is_array($options['params'])) {
            $data['params'] = [];
        }

        $validation = Validator::make($data, self::rules());

        return $validation->fails()
            ? new InvalidParams($validation->errors()->toArray())
            : $options;
    }

    /**
     * Defines the rules for validating a JSON-RPC 2.0 request.
     *
     * @return array
     */
    public static function rules(): array
    {
        return [
            'jsonrpc' => 'required|in:"2.0"',
            'method'  => 'required|string',
            'params'  => 'array',
            'id'      => new Identifier(),
        ];
    }
}
