<?php

namespace App\Objectbase\Application\Actions;

use Psr\Http\Message\ResponseInterface as Response;

class GetOne extends Base
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $objectbaseId = (int)$this->resolveArg('id');
        $objectbase = $this->getFindService()->getObjectbase($objectbaseId);

        return $this->respondWithData($objectbase);
    }
}
