<?php

namespace App\Objectbase\Application\Services;

use App\Objectbase\Domain\IObjectbaseRepository;
use Sophy\Application\Services\BaseService;

abstract class Base extends BaseService
{
    protected IObjectbaseRepository $objectbaseRepository;

    public function __construct(IObjectbaseRepository $objectbaseRepository)
    {
        $this->objectbaseRepository = $objectbaseRepository;
    }

    protected function getObjectbaseFromDb($objectbaseId)
    {
        return $this->objectbaseRepository->checkAndGetObjectbaseOrFail($objectbaseId);
    }
}
