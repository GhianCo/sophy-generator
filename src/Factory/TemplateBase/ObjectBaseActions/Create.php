<?php

namespace App\Objectbase\Application\Actions;

use Psr\Http\Message\ResponseInterface as Response;

class Create extends Base
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $input = (array)$this->request->getParsedBody();
        $objectbase = $this->getCreateService()->create($input);

        return $this->respondWithData($objectbase, 'Objectbase creado con éxito');
    }
}
