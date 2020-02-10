<?php

declare(strict_types=1);

namespace Sajya\Server\Http;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Sajya\Server\Exceptions\InvalidParams;

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
                ->map(fn($options) => $this->checkValidation($options))
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
     * @param $options
     *
     * @return InvalidParams|array
     */
    public function checkValidation(array $options = [])
    {
        $validation = Validator::make($options, self::rules());

        return $validation->fails()
            ? new InvalidParams($validation->errors()->toArray())
            : $options;
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
