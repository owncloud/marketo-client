<?php

namespace MarketoClient\Request;

use MarketoClient\RequestInterface;

class PushLeadBy implements RequestInterface
{

    private $lookupField;
    private $programName;
    private $leadData;
    private $leadSource;
    private $reason;

    private $requestBody;

    public function __construct(string $lookupField, array $leadData)
    {
        $this->leadData = $leadData;

        if (!isset($leadData[$this->lookupField]) || !$leadData[$this->lookupField]) {
            throw new \InvalidArgumentException("Lookup field $lookupField not found in lead data.");
        }

        $this->requestBody = [
            'action' => 'createOrUpdate',
            'lookupField' => $this->lookupField,
            'input' => [$this->leadData]
        ];
    }

    public function getMethod(): string
    {
        return 'POST';
    }

    public function getPath(): string
    {
        return 'leads/push.json';
    }

    public function getQuery(): array
    {
        return [];
    }

    public function getLookupField(): ?string
    {
        return $this->lookupField;
    }

    /**
     * @return string
     */
    public function getProgramName(): ?string
    {
        return $this->programName;
    }

    /**
     * @param string $programName
     * @return PushLeadBy
     */
    public function setProgramName(string $programName): PushLeadBy
    {
        $this->programName = $programName;
        $this->requestBody['programName'] = $programName;

        return $this;
    }

    public function getLeadData(): array
    {
        return $this->leadData;
    }

    public function getLeadSource(): ?string
    {
        return $this->leadSource;
    }

    public function setLeadSource(string $leadSource): PushLeadBy
    {
        $this->leadSource = $leadSource;
        $this->requestBody['source'] = $leadSource;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     * @return PushLeadBy
     */
    public function setReason(string $reason): PushLeadBy
    {
        $this->reason = $reason;
        $this->requestBody['reason'] = $reason;

        return $this;
    }

    public function jsonSerialize()
    {
        return $this->requestBody;
    }
}

