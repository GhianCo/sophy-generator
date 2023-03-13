<?php

namespace App\Objectbase\Application\Actions;

use Sophy\Validation\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Respect\Validation\Validator as RespectValidator;

final class CreateValidator implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $rules = array(
            // Example: 'name' => RespectValidator::alnum()->noWhitespace()->length(1, 10),
        );

        $validation = new Validator();
        return $validation->validate($rules, $request, $handler);
    }
}