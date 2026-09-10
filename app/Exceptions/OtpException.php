<?php

namespace App\Exceptions;

use RuntimeException;

class OtpException extends RuntimeException
{
    public function __construct(
        string $message,
        private int $statusCode = 422,
        private ?int $retryAfter = null
    ) {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function retryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
