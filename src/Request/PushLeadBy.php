<?php

namespace MarketoClient\Request;


class PushLeadBy implements RequestInterface
{

    private $lookupField;
    private $programName;
    private $leadData;

    public function __construct($lookupField, $programName, array $leadData)
    {
        $this->lookupField = $lookupField;
        $this->programName = $programName;
        $this->leadData = $leadData;

        if (!isset($leadData[$this->lookupField]) || !$leadData[$this->lookupField]) {
            throw new \InvalidArgumentException("Lookup field $lookupField not found in lead data.");
        }
    }


    public function getMethod()
    {
        return 'POST';
    }

    public function getPath()
    {
        return 'leads/push.json';
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'action' => 'createOrUpdate',
            'lookupField' => $this->lookupField,
            'programName' => $this->programName,
            'input' => [$this->leadData]
        ];
    }
}