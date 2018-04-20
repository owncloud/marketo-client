<?php


namespace MarketoClient\Request;


class ProgramStatus implements \JsonSerializable
{
    private $programId;
    private $leadId;
    private $status;

    public function __construct($programId, $leadId, $status)
    {
        $this->programId = $programId;
        $this->leadId = $leadId;
        $this->status = $status;
    }


    public function getProgramId()
    {
        return $this->programId;
    }


    public function getLeadId()
    {
        return $this->leadId;
    }


    public function getStatus()
    {
        return $this->status;
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
            'input' => [[
                'id' => $this->leadId
            ]],
            'status' => $this->status
        ];
    }
}