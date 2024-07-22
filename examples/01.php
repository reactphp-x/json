<?php

require __DIR__ . "./../vendor/autoload.php";

use Reactphp\Framework\Json\Json;


$json = new Json();

$json->registerDataSource('post', [
    'id' => 1,
    'title' => 'first post',
]);
$json->registerDataSource('post1', function ($json, $config) {
    return [
        'id' => 2,
        'title' => 'second post',
    ];
});

$json->registerDataStructure('post', [
    'id' => ":id",
]);

$json->registerDataStructure('post1', function ($json, $config) {
    return [
        'title' => ":title",
    ];
});

// output : {"id":1,"title":"first post"}
echo json_encode($json->getJson([
    "_data_source" => "post",
])) . PHP_EOL;

// output : {"id":2,"title":"second post"}
echo json_encode($json->getJson([
    "_data_source" => "post1",
])) . PHP_EOL;

// output : {"id":1}
echo json_encode($json->getJson([
    "_data_source" => "post",
    "_data_structure" => [
        "id"=> ":id",
    ],
])) . PHP_EOL;

// output : {"id":1}
echo json_encode($json->getJson([
    "_data_source" => "post",
    "_data_structure" => "post",
])) . PHP_EOL;

// output : {"title":"second post"}
echo json_encode($json->getJson([
    "_data_source" => "post1",
    "_data_structure" => "post1",
])) . PHP_EOL;


// _data_option
$json->registerDataSource('test_option', function ($json, $config) {
    return $config['_data_option'] ?? [];
});


// output : {"id":"1","title":"my is title"}
echo json_encode($json->getJson([
    "_data_source" => "test_option",
    "_data_option"=> [
        "id"=> "1",
        "title"=> "my is title",
    ],
    "_data_structure" => ":*",
])) . PHP_EOL;