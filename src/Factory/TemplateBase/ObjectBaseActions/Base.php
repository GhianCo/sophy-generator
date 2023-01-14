<?php

namespace App\Objectbase\Application\Actions;

use App\Objectbase\Application\Services\CreateService;
use App\Objectbase\Application\Services\FindService;
use App\Objectbase\Application\Services\UpdateService;
use Sophy\Application\Actions\Action;

abstract class Base extends Action
{
    protected function getCreateService()
    {
        return $this->container->get(CreateService::class);
    }

    protected function getUpdateService()
    {
        return $this->container->get(UpdateService::class);
    }

    protected function getFindService(): FindService
    {
        return $this->container->get(FindService::class);
    }
}
