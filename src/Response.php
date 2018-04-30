<?php
namespace MarketoClient;

use MarketoClient\Response\Error;

class Response
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return Error[]
     */
    public function getResult(): array
    {
        return isset($this->data['result'])? $this->data['result']: [];
    }


    public function getErrors(): \Iterator
    {
        if (isset($this->data['errors']) && \count($this->data['errors'])) {
            foreach ($this->data['errors'] as $error) {
                yield new Error($error['code'], $error['message']);

            }
        }

        return new \EmptyIterator();
    }


    public function isSuccessful(): bool
    {
        return true === $this->data['success'];
    }
}
