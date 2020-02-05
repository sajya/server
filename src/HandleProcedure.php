<?php

declare(strict_types=1);

namespace Sajya\Server;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sajya\Server\Http\Request;
use Sajya\Server\Http\Response;
use Sajya\Server\Lines\MethodDetect;
use Sajya\Server\Lines\UsefulWork;
use Sajya\Server\Lines\ValidationRequestFormat;
use Sajya\Server\Lines\ValidationRequestParams;

class HandleProcedure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var Pipeline
     */
    protected Pipeline $pipeline;

    /**
     * @var Guide
     */
    protected Guide $guide;

    /**
     * Create a new job instance.
     *
     * @param Guide   $guide
     * @param Request $request
     */
    public function __construct(Guide $guide, Request $request)
    {
        $this->guide = $guide;
        $this->request = $request;
        $this->pipeline = app(Pipeline::class);
    }

    /**
     * Execute the job.
     *
     * @return Response
     */
    public function handle(): Response
    {
        return $this->pipeline
            ->send(new State($this->guide, $this->request))
            ->through([
                MethodDetect::class,
                ValidationRequestFormat::class,
                ValidationRequestParams::class,
                UsefulWork::class,
            ])
            ->via('run')
            ->then(fn(State $state) => $state->getResponse());
    }
}
