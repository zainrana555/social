<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractApiController extends AbstractController
{
    protected function respond($message, $data = [], int $statusCode = Response::HTTP_OK): Response
    {
        return $this->json([
            'message'   => $message,
            'data'      => $data,
        ], $statusCode);
    }
}
