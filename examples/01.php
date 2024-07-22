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


$json->registerDataSource('users', function ($json, $config) {
    $userId = $config['_data_option']['user_id'] ?? 0;
    $users = [
        [
            'id' => 1,
            'name' => 'my first name',
        ],
        [
            'id' => 2,
            'name' => 'my second name',
        ],
    ];

    if ($userId) {
        foreach ($users as $user) {
            if ($user['id'] == $userId) {
                return $user;
            }
        }
        return null;
    } else {
        return $users;
    }
});

$json->registerDataSource('posts', [
   [
         'id' => 1,
         'title' => 'first post',
         'user_id' => 1,
    ],
    [
         'id' => 2,
         'title' => 'second post',
         'user_id' => 2,

    ],
]);

$json->registerDataStructure('posts', [
    '_is_support_array' => true, 
    'id' => ":id",
    'title' => ":title",
    'user_id' => ":user_id",
]);

// output : [{"id":1,"title":"first post","user_id":1},{"id":2,"title":"second post",,"user_id":2}]
echo json_encode($json->getJson([
    "_data_source" => "posts",
    "_data_structure" => 'posts',
])) . PHP_EOL;

// output with user : [{"id":1,"title":"first post","user":{"id":1,"name":"my first name"}},{"id":2,"title":"second post","user":{"id":1,"name":"my second name"}}]
echo json_encode($json->getJson([
    "_data_source" => "posts",
    "_data_structure" => [
        '_is_support_array' => true, 
        'id' => ":id",
        'title' => ":title",
        'user' => [
            "_data_source" => "users",
            "_data_option" => [
                'user_id' => ":user_id",
            ],
            "_data_structure" => [
                'id' => ":id",
                'name' => ":name",
            ],
        ]
    ],
])) . PHP_EOL;