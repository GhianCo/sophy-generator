<?php 

namespace App\Objectbase\Application\Services;

use App\Objectbase\Domain\Entities\Objectbase;
use App\Objectbase\Domain\Exceptions\ObjectbaseException;
use App\Utils\FieldValidator;
use App\Utils\GenericUtils;

final class CreateService extends Base
{

    use FieldValidator;

    private $fieldsRequired = array();

    public function create($input)
    {
        $resquestBody = $this->validateObjectbaseData($input);
        $objectbase = new Objectbase($resquestBody);

        $objectbaseId = $this->objectbaseRepository->insert($objectbase);
        $objectbase->setObjectbase_id($objectbaseId);

        return $objectbase;
    }

    private function validateObjectbaseData($input)
    {
        $fieldsException = $this->validator($input);

        if (count($fieldsException)) {
            throw new ObjectbaseException('El/los campos ' . GenericUtils::arrayValuesToString($fieldsException, ", ") . ' son requerido(s).', 400);
        }

        return $input;
    }
}
?>