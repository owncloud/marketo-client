<?php


namespace MarketoClient\Request;


interface RequestInterface extends \JsonSerializable
{
    public function getMethod();
    public function getPath();
}