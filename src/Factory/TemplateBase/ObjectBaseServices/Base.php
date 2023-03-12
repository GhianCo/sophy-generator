<?php

namespace App\Objectbase\Application\Services;

use App\Objectbase\Domain\IObjectbaseRepository;
use Sophy\Application\Services\BaseService;
use App\Objectbase\Application\DTO\ObjectbaseDTO;
use App\Objectbase\Domain\Entities\Objectbase;
abstract class Base extends BaseService
{
    protected IObjectbaseRepository $objectbaseRepository;

    public function __construct(IObjectbaseRepository $objectbaseRepository)
    {
        parent::__construct();
        $this->objectbaseRepository = $objectbaseRepository;
        $this->config->registerMapping(Objectbase::class, ObjectbaseDTO::class);
    }

    protected function getObjectbaseFromDb($objectbaseId)
    {
        return $this->objectbaseRepository->checkAndGetObjectbaseOrFail($objectbaseId);
    }
}
