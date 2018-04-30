<?php

namespace MarketoClient\Client;


class AccessToken
{
    private $token;
    private $validUntil;

    public function __construct(string $token, int $validUntil)
    {
        $this->token = $token;
        $this->validUntil = $validUntil;
    }

    /**
     * @return string
     */
    public function getBearerToken(): string
    {
        return $this->token;
    }

    /**
     * @return int
     */
    public function getValidUntil(): int
    {
        return $this->validUntil;
    }

    public function hasExpired(): bool
    {

        return time() > $this->validUntil;
    }

    public function __toString(): string
    {
        return $this->token;
    }
}

