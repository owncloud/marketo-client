<?php
namespace MarketoClient\Response;

class Error
{

    private $code;
    private $message;


    public function __construct($code, $message)
    {
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }


    public function tokenHasExpired() {
        return $this->code === 602;
    }
}
