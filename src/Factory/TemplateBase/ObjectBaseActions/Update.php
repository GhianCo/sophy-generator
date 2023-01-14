<?php

namespace App\Objectbase\Application\Actions;

use Psr\Http\Message\ResponseInterface as Response;

class Update extends Base
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $input = (array)$this->request->getParsedBody();
        $objectbaseId = (int)$this->resolveArg('id');

        $objectbase = $this->getUpdateService()->update($input, $objectbaseId);

        return $this->respondWithData($objectbase, 'Objectbase actualizado con éxito');
    }

}
