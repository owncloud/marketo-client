<?php
use PHPUnit\Framework\TestCase;
use MarketoClient\Request\CustomObject;

class CustomObjectTest extends TestCase {

    public function testCustomObject() {
        $customObject = new CustomObject('myobject', CustomObject::CREATE_ONLY, [
             'username' => 'hans',
             'somekey' => 'somevalue'
         ]);

        $this->assertEquals($customObject->getMethod(), 'POST');
        $this->assertEquals($customObject->getPath(), 'customobjects/myobject.json');

        $body = $customObject->jsonSerialize();

        $this->assertEquals([
            'action' => CustomObject::CREATE_ONLY,
            'dedupeBy' => 'dedupeFields',
            'input' => [
                'username' => 'hans',
                'somekey' => 'somevalue'
            ]
        ], $body);
    }

    public function testDeleteCustomObject() {
        $customObject = new CustomObject('myobject', CustomObject::DELETE, [
            ['id' => 123],
            ['id' => 456],
        ]);

        $this->assertEquals($customObject->getMethod(), 'POST');
        $this->assertEquals($customObject->getPath(), 'customobjects/myobject/delete.json');

        $body = $customObject->jsonSerialize();

        $this->assertEquals([
            'deleteBy' => 'dedupeFields',
            'input' => [
                ['id' => 123],
                ['id' => 456],
            ]
        ], $body);
    }




}