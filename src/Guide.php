<?php

declare(strict_types=1);

namespace Sajya\Server;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Sajya\Server\Http\Parser;
use Sajya\Server\Http\Request;
use Sajya\Server\Http\Response;

class Guide
{
    /**
     * Stores all available RPC commands
     *
     * @var Collection
     */
    protected $map;

    /**
     * Guide constructor.
     *
     * @param array $map
     */
    public function __construct(array $map = [])
    {
        $this->map = collect($map);
    }

    /**
     * @param string|null $content
     *
     * @return string
     */
    public function handle(string $content = null): string
    {
        $parser = new Parser($content);
        $rpcRequests = $parser->makeRequests();

        $result = collect($rpcRequests)
            ->map(fn(Request $request) => $this->handleProcedure($request));

        $response = $parser->isBatch() ? $result->all() : $result->first();

        return json_encode($response, JSON_THROW_ON_ERROR, 512);
    }

    /**
     * @param \Sajya\Server\Http\Request $request
     *
     * @return Response
     */
    public function handleProcedure(Request $request): Response
    {
        $procedure = $this->findProcedure($request);


        if ($procedure === null) {
            $this->makeResponse($request, new Exception('Method "' . $request->getMethod() . '" not found '));
        }

        $params = $request->getParams();

        $validation = Validator::make($params->toArray(), $procedure->rules($params));

        if($validation->fails()){
            \request()->validate();
            dd('invalid');
            //$validation->getMessageBag()->toArray();
            //$validation->getMessageBag()->getMessages();
            //$this->makeResponse($request, new Exception('Method "' . $request->getMethod() . '" not found '));
        }

        $params = collect($validation->validated());

        $result = $procedure->handle($params);


        return $this->makeResponse($request, $result);
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

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            $this->handle($request->getContent())
        );
    }

    /**
     * @param Request $request
     * @param mixed   $result
     *
     * @return Response
     */
    private function makeResponse(Request $request, $result): Response
    {
        return tap(new Response(), static function (Response $response) use ($request, $result) {
            $response->setId($request->getId());
            $response->setVersion($request->getVersion());
            $response->setResult($result);
        });
    }
}
