<?php


namespace MarketoClient\Request;

use MarketoClient\RequestInterface;

class SetProgramStatus implements RequestInterface
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

    public function getMethod(): string
    {
        return 'POST';
    }

    public function getPath(): string
    {
        return "leads/programs/{$this->programId}/status.json";
    }

    public function getQuery(): array
    {
        return [];
    }

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

