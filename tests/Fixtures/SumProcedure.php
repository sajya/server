<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Fixtures;

use Illuminate\Support\Collection;
use Sajya\Server\Procedure;

class SumProcedure extends Procedure
{
    /**
     * The name of the procedure that will be
     * displayed and taken into account in the search
     *
     * @var string
     */
    public static string $name = 'sum';

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'a' => 'integer|required',
            'b' => 'integer|required',
        ];
    }

    /**
     * @param Collection $params
     *
     * @return array|int|string|void
     */
    public function handle(Collection $params)
    {
        return  $params->get('a') + $params->get('b');
    }
}
