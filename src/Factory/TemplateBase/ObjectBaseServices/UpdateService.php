<?php

namespace App\Objectbase\Application\Services;

use App\Objectbase\Domain\Entities\Objectbase;
use App\Objectbase\Domain\Exceptions\ObjectbaseException;
use App\Utils\FieldValidator;
use App\Utils\GenericUtils;

final class UpdateService extends Base
{

    use FieldValidator;

    private $fieldsRequired = array('objectbase_id');

    public function update($input, $objectbaseId)
    {
        $resquestBody = $this->validateObjectbaseData($input, $objectbaseId);
        $objectbaseToPrepared = new Objectbase($resquestBody);

        $this->objectbaseRepository->update($objectbaseToPrepared);

        return $objectbaseToPrepared;
    }

    private function validateObjectbaseData($input, $objectbaseId)
    {
        $fieldsException = $this->validator($input);

        if (count($fieldsException)) {
            throw new ObjectbaseException('El/los campos ' . GenericUtils::arrayValuesToString($fieldsException, ", ") . ' son requerido(s).', 400);
        }

        $this->objectbaseRepository->checkAndGetObjectbaseOrFail($objectbaseId);

        return $input;
    }
}

?>