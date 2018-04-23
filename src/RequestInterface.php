<?php


namespace MarketoClient;


interface RequestInterface extends \JsonSerializable
{
    public function getMethod();
    public function getPath();
}