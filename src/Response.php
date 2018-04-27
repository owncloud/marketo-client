<?php
namespace MarketoClient;

class Response
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getResult()
    {
        return isset($this->data['result'])? $this->data['result']: [];
    }


    public function getError()
    {
        if (isset($this->data['errors']) && count($this->data['errors'])) {
            return $this->data['errors'][0];
        }

        return [];
    }


    public function isSuccessful()
    {
        return true === $this->data['success'];
    }

}