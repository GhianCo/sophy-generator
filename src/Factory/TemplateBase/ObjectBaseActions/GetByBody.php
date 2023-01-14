<?php

namespace App\Objectbase\Application\Actions;

use Psr\Http\Message\ResponseInterface as Response;

class GetByBody extends Base
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $input = (array)$this->request->getParsedBody();
        $objectbase = $this->getFindService()->searchByParams($input);

        return $this->respondWithData($objectbase['data'], 'Lista de objectbasees por body', $objectbase['pagination']);
    }
}
