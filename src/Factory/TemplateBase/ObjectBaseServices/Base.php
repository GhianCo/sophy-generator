<?php

namespace App\Objectbase\Application\Services;

use App\Objectbase\Domain\ObjectbaseRepository;
use Sophy\Application\Services\BaseService;

abstract class Base extends BaseService
{
    protected ObjectbaseRepository $objectbaseRepository;

    public function __construct(ObjectbaseRepository $objectbaseRepository)
    {
        $this->objectbaseRepository = $objectbaseRepository;
    }

    protected function getObjectbaseFromDb($objectbaseId)
    {
        return $this->objectbaseRepository->checkAndGetObjectbaseOrFail($objectbaseId);
    }
}
