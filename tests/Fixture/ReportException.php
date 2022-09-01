<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Fixture;

use Exception;

class ReportException extends Exception
{
    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report()
    {
        config()->set('render-response-exception', 'Enabled');

        return true;
    }
}
