<?php

namespace MarketoClient\Request;


use MarketoClient\RequestInterface;

class AddToList implements RequestInterface
{

    private $listId;
    private $leadIds;

    public function __construct(int $listId, array $leadIds)
    {
        $this->listId = $listId;
        $this->leadIds = array_unique($leadIds);
    }

    public function getMethod(): string
    {
        return 'POST';
    }

    public function getPath(): string
    {
        return "lists/{$this->listId}/leads.json";
    }

    public function getQuery(): array
    {
        return [];
    }

    public function jsonSerialize()
    {
        return ['input' => array_map(function($id) {
            return ['id' => $id];
        }, $this->leadIds)];
    }
}

