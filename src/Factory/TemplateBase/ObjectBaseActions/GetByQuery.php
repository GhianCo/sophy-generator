<?php

namespace App\Objectbase\Application\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Sophy\Constants;

class GetByQuery extends Base
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $queryParams = $this->request->getQueryParams();
        $querySearch = isset($queryParams['q']) ? $queryParams['q'] : Constants::UNDEFINED;

        $objectbase = $this->getFindService()->getBySearch($querySearch);

        return $this->respondWithData($objectbase, 'Lista de objectbasees por query');
    }
}
