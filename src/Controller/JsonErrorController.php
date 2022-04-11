<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class JsonErrorController
{
    public function show(Throwable $exception, LoggerInterface $logger = null): JsonResponse
    {
        return new JsonResponse(['errors' => ["global" => [$exception->getMessage()]]]);
    }
}