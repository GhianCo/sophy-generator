<?php

namespace App\Objectbase\Application\Actions;

use Psr\Http\Message\ResponseInterface as Response;

class GetAll extends Base
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $queryParams = $this->request->getQueryParams();
        $page = isset($queryParams['page']) ? $queryParams['page'] : null;
        $perPage = isset($queryParams['perPage']) ? $queryParams['perPage'] : null;

        $objectbase = $this->getFindService()->getObjectbaseesByPage((int)$page, (int)$perPage);

        return $this->respondWithData($objectbase['data'], 'Lista de objectbasees', $objectbase['pagination']);
    }
}
