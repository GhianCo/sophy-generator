<?php

namespace App\Objectbase\Infrastructure;

use App\Objectbase\Domain\Entities\Objectbase;
use App\Objectbase\Domain\Exceptions\ObjectbaseException;
use App\Objectbase\Domain\IObjectbaseRepository;
use Sophy\Infrastructure\BaseRepositoryMysql;

class ObjectbaseRepositoryMysql extends BaseRepositoryMysql implements IObjectbaseRepository
{
    /**
     * {@inheritdoc}
     */
    public function checkAndGetObjectbaseOrFail(int $id): Objectbase
    {

        $whereParams = array(
            array("field" => "objectbase_id", "value" => $id, "operator" => "=")
        );

        $objectbase = $this->setWhereParams($whereParams)->execQueryRow();

        if (!$objectbase) {
            throw new ObjectbaseException('No se encontró el objectbase con id: ' . $id . '.', 404);
        }

        return $objectbase;
    }
}
