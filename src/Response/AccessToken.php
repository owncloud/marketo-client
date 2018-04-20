<?php

namespace MarketoClient\Response;


class AccessToken
{

    private $token;
    private $expiresIn;

    public function __construct($token, $expiresIn)
    {
        $this->token = $token;
        $this->expiresIn = $expiresIn;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    public function hasExpired()
    {
        return $this->expiresIn - time() <= 0;
    }

    public function __toString()
    {
        return $this->token;
    }
}
