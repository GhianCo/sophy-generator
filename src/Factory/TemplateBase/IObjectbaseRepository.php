<?php

namespace App\Objectbase\Domain;

use App\Objectbase\Domain\Entities\Objectbase;
use Sophy\Domain\BaseRepository;

interface ObjectbaseRepository extends BaseRepository
{
    public function checkAndGetObjectbaseOrFail(int $id): Objectbase;
}
