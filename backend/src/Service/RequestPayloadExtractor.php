<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class RequestPayloadExtractor
{
    public function __construct(private readonly DecoderInterface $decoder)
    {
    }

    public function extractJson(Request $request): array
    {
        try {
            return $this->decoder->decode($request->getContent(), 'json');
        } catch (\Throwable) {
            throw new ValidationException('Invalid JSON payload');
        }
    }
}
