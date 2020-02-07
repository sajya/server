<?php

declare(strict_types=1);

namespace Sajya\Server\Http;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
            $validation = Validator::make($decode, $this->rules());
            $validation->fails();
            //$this->isParseError = !$validation->fails();
        } catch (Exception $e) {

            $this->decode = collect();
            $this->isParseError = true;
        } catch (\TypeError $exception) {
            $this->decode = collect();
            $this->isParseError = true;
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        Validator::extend('rule_id', function($attribute, $value, $parameters)
        {
            return is_null($value)||is_int($value)||is_string($value);
        });
        return collect([
            'jsonrpc' => 'required|in:"2.0"',
            'method'  => 'required|string',
            'params'  => 'array',
            'id'      => 'rule_id'
        ])
            ->when($this->batching, static function (Collection $collection) {
                return $collection->keyBy(fn(string $key) => Str::start($key, '*.'));
            })
            ->toArray();
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
