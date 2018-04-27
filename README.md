##Usage Example
```php
<?php

include_once 'vendor/autoload.php';

use MarketoClient\Client;
use MarketoClient\Request\PushLeadBy;

$client = new Client('https://xyz-abc-123.mktorest.com', 'your-client-id', 'your-client-secret');



try {

    $response = $client->execute(new PushLeadBy('email', 'oc.org test', [
        'email' => 'jwoo@example.com',
        'firstName' => 'John',
        'lastName' => 'Woo'
    ]));
} catch (\Exception $e) {
    // Handle connection exceptions here
}

if (!$response->isSuccessful()) {
    var_dump($response->getError());
    exit;
    
}

var_dump($response->getResult());
```