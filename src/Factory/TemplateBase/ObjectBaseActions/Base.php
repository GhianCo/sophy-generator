<?php

namespace App\Objectbase\Application\Actions;

use App\Objectbase\Application\Services\CreateObjectbaseService;
use App\Objectbase\Application\Services\FindObjectbaseService;
use App\Objectbase\Application\Services\UpdateObjectbaseService;
use Sophy\Application\Actions\Action;

abstract class Base extends Action
{
    protected function getCreateObjectbaseService()
    {
        return $this->container->get(CreateObjectbaseService::class);
    }

    protected function getUpdateObjectbaseService()
    {
        return $this->container->get(UpdateObjectbaseService::class);
    }

    protected function getFindObjectbaseService(): FindObjectbaseService
    {
        return $this->container->get(FindObjectbaseService::class);
    }
}
