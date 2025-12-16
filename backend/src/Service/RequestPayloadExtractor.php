<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ValidationException;
use JsonException;
use Symfony\Component\HttpFoundation\Request;

class RequestPayloadExtractor
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function extractJson(Request $request): array
    {
        try {
            return json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new ValidationException('Invalid JSON payload');
        }
    }
}
