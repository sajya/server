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
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Exceptions\RuntimeRpcException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HandleProcedure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Procedure
     */
    protected Procedure $procedure;

    /**
     * Create a new job instance.
     *
     * @param Procedure $procedure
     */
    public function __construct(Procedure $procedure)
    {
        $this->procedure = $procedure;
    }

    /**
     * Execute the job.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            return App::call(get_class($this->procedure) . '@handle');
        } catch (HttpException | RuntimeException | Exception $exception) {

            $message = $exception->getMessage();

            $code = method_exists($exception, 'getStatusCode')
                ? $exception->getStatusCode()
                : $exception->getCode();

            if ($exception instanceof ValidationException) {
                return new InvalidParams($exception->validator->errors()->toArray());
            }

            return new RuntimeRpcException($message, $code);
        }
    }
}
