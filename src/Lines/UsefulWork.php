<?php

declare(strict_types=1);

namespace Sajya\Server\Lines;

use RuntimeException;
use Sajya\Server\Exceptions\RuntimeRpcException;
use Sajya\Server\Line;
use Sajya\Server\State;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UsefulWork extends Line
{
    /**
     * @param State $state
     *
     * @return State
     * @throws \Throwable
     */
    public function handler(State $state): State
    {
        try {
            $params = $state->getRequest()->getParams();
            $result = $state->getProcedure()->handle($params);
        } catch (HttpException $exception) {

            $data = config('app.debug')
                ? $exception->getTrace()
                : [];

            $exception = new RuntimeRpcException($exception->getMessage(), $exception->getStatusCode());
            $exception->setData($data);


            return $state->makeResponse($exception);

        } catch (RuntimeException $exception) {

            $data = config('app.debug')
                ? $exception->getTrace()
                : [];

            $exception = new RuntimeRpcException($exception->getMessage(), $exception->getCode());
            $exception->setData($data);

            return $state->makeResponse($exception);
        }

        return $state->makeResponse($result);
    }
}
