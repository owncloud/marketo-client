<?php


namespace MarketoClient\Request;


use MarketoClient\RequestInterface;


/**
 * Usage:
 *  $response = $client->execute(new CustomObject('myobject', CustomObject::CREATE_ONLY, [
 *      'username' => 'hans',
 *      'somekey' => 'somevalue'
 * ]));
 *
 * $response = $client->execute(new CustomObject('myobject', CustomObject::DELETE, [
 *      ['id' => 123],
 *      ['id' => 456],
 *  ]));
 *
 * @package MarketoClient\Request
 */
class CustomObject implements RequestInterface
{

    public const CREATE_ONLY = 'createOnly';
    public const UPDATE_ONLY = 'updateOnly';
    public const CREATE_OR_UPDATE = 'createOrUpdate';
    public const DELETE = 'delete';

    /** @var string  */
    private $objectName;
    /** @var string  "createOnly", "updateOnly", "createOrUpdate", "delete" */
    private $action;
    /** @var string[] */
    private $input;

    public function __construct(string $objectName, string $action, array $input)
    {
        $this->objectName = $objectName;
        $this->action = $action;
        $this->input = $input;

    }

    public function getMethod(): string
    {
        return 'POST';
    }

    public function getPath(): string
    {
        if ($this->action !== 'delete') {
            return "customobjects/{$this->objectName}.json";
        }

        return "customobjects/{$this->objectName}/delete.json";
    }

    public function getQuery(): array
    {
        return [];
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        if ($this->action !== 'delete') {
            return [
                'action' => $this->action,
                'dedupeBy' => 'dedupeFields',
                'input' => $this->input,
            ];
        }

        return [
            'deleteBy' => 'dedupeFields',
            'input' => $this->input,
        ];
    }
}
