<?php

declare(strict_types=1);

namespace Sajya\Server;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Sajya\Server\Exceptions\InternalErrorException;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Exceptions\RuntimeRpcException;
use Sajya\Server\Facades\RPC;
use Sajya\Server\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HandleProcedure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    protected string $procedure;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Create a new job instance.
     *
     * @param string  $procedure
     * @param Request $request
     */
    public function __construct(string $procedure, Request $request)
    {
        $this->procedure = $procedure;
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $parameters = RPC::bindResolve($this->procedure, $this->request->getParams());

            return App::call($this->procedure, $parameters);
        } catch (HttpException | RuntimeException | Exception $exception) {
            $message = $exception->getMessage();

            $code = method_exists($exception, 'getStatusCode')
                ? $exception->getStatusCode()
                : $exception->getCode();

            if ($exception instanceof ValidationException) {
                return new InvalidParams($exception->validator->errors()->toArray());
            }

            if ($code === 500) {
                return new InternalErrorException();
            }

            if (! is_int($code)) {
                $code = -1;
            }

            return new RuntimeRpcException($message, $code);
        }
    }
}
