<?php

namespace Sajya\Server;

use Sajya\Server\Http\Request;
use Sajya\Server\Http\Response;

class State
{
    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var Response|null
     */
    protected $response;

    /**
     * @var Procedure|null
     */
    protected ?Procedure $procedure;

    /**
     * @var Guide
     */
    protected Guide $guide;

    /**
     * State constructor.
     *
     * @param Guide   $guide
     * @param Request $request
     */
    public function __construct(Guide $guide, Request $request)
    {
        $this->guide = $guide;
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return Response|null
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * @param Response $response
     *
     * @return State
     */
    public function setResponse(Response $response): State
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return Procedure|null
     */
    public function getProcedure(): ?Procedure
    {
        return $this->procedure;
    }

    /**
     * @param Procedure $procedure
     *
     * @return State
     */
    public function setProcedure(Procedure $procedure): State
    {
        $this->procedure = $procedure;

        return $this;
    }

    /**
     * @return Guide
     */
    public function getGuide(): Guide
    {
        return $this->guide;
    }

    /**
     * @param mixed $result
     *
     * @return State
     */
    public function makeResponse($result): State
    {
        $response = tap(new Response(), function (Response $response) use ($result) {
            $response->setId($this->request->getId());
            $response->setVersion($this->request->getVersion());
            $response->setResult($result);
        });

        return $this->setResponse($response);
    }
}
