<?php


namespace MarketoClient;


interface RequestInterface extends \JsonSerializable
{
    public function getMethod(): string;
    public function getPath(): string;
    public function getQuery(): array;
}