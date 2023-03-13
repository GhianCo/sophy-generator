<?php

use App\Objectbase\Application\Actions\Create;
use App\Objectbase\Application\Actions\GetAll;
use App\Objectbase\Application\Actions\Update;
use App\Objectbase\Application\Actions\GetOne;
use App\Objectbase\Application\Actions\GetByQuery;
use App\Objectbase\Application\Actions\GetByBody;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Sophy\Middleware\SessionMiddleware;
use App\Objectbase\Application\Actions\CreateObjectbaseValidator;

return function (Group $group) {
    $group->group('/objectbase', function (Group $group) {

        $group->get('', GetAll::class);
        $group->get('/byQuery', GetByQuery::class);
        $group->get('/{id}', GetOne::class);

        $group->post('', Create::class)->add(CreateObjectbaseValidator::class);
        $group->post('/byBody', GetByBody::class);

        $group->put('/{id}', Update::class);

    })->add(SessionMiddleware::class);
}

?>