<?php 

namespace Reactphp\Framework\Json\Tests;

use Reactphp\Framework\Json\Json;



class JsonTest extends TestCase
{
    protected $json;

    public function setUp(): void
    {
        parent::setUp();
        $this->json = new Json();
        $this->json->registerDataSource('user', [
            'id' => 1,
            'name' => 'Hello User',
        ]);

        $this->json->registerDataSource('post', [
            'id' => 2,
            'name' => 'Hello post',
        ]);

        $this->json->registerDataSource('comment', [
            'id' => 3,
            'name' => 'Hello comment',
        ]);

        $this->json->registerDataSource('user_post', [
            "_data_source" => "user",
            "_data_structure" => [
                "id" => ":id",
                "name" => ":name",
                "post" => [
                    "_data_source" => "post",
                    "_data_structure" => [
                        "id" => ":id",
                        "name" => ":name"
                    ]
                ]
            ]
        ]);

        $this->json->registerDataSource('user_function', function ($json, $config = []) {
            return [
                'id' => 3,
                'name' => 'Hello user_function',
                "data_option" => $config['_data_option'] ?? []
            ];
        });

        $this->json->registerDataSource('user_function_json', function ($json, $config = []) {
            return [
                'id' => 10,
                'name' => 'Hello user_function_json',
                "data_option" => $config['_data_option'] ?? [],
                'post' => $json->getJson([
                    "_data_source" => "post"
                ])
            ];
        });

        $this->json->registerDataSource('user_function_quote', [
            "_data_source" => "user_function",
            "_data_context" => [
                "id" => ":id",
                "default" => 1
            ],
            "_data_option" => "user_option",
        ]);

        $this->json->registerDataOption('user_option', [
            "user_id" => ':id',
            "default_user_id" => 1
        ]);

        $this->json->registerDataStructure('user', [
            'id' => ":id",
            'name' => ':name',
        ]);

        $this->json->registerDataStructure('user_structure', [
            'id' => [
                "_data_structure" => "id",
            ],
            'name' => [
                "_data_structure" => "name",
            ],
        ]);

        $this->json->registerDataStructure('post', [
            'id' => ":id",
            'name' => ':name',
        ]);

        $this->json->registerDataStructure('user_post_structure', [
            "_data_source" => "user",
            "_data_structure" => [
                "id" => ":id",
                "name" => ":name",
                "post" => [
                    "_data_source" => "post",
                    "_data_structure" => [
                        "id" => ":id",
                        "name" => ":name"
                    ]
                ]
            ]
        ]);

        $this->json->registerDataStructure('post_structure', [
            'id' => [
                "_data_structure" => "id",
            ],
            'name' => [
                "_data_structure" => "name",
            ],
        ]);

        $this->json->registerDataStructure("id", ":id");
        $this->json->registerDataStructure("name", ":name");

    }


    public function testBaseJson()
    {
        $array = $this->json->getJson([
            "user" => ":user",
            "user_1" => [
                "_data_source" => "user",
            ],
            "user_1-1" => [
                "_data_source" => [
                    "_data_source" => "user",
                ],
            ],
            "user_1-2" => [
                "_data_source" => [
                    "_data_source" => "user",
                    "_data_structure" => "user"
                ],
            ],
            "user_1-3" => [
                "_data_source" => [
                    "_data_source" => "user",
                    "_data_structure" => [
                        "id" => ":id",
                        "name" => ":name"
                    ]
                ],
            ],
            "user_2" => [
                "_data_source" => "user",
                "_data_structure" => "user"
            ],
            "user_structure" => [
                "_data_source" => "user",
                "_data_structure" => "user_structure"
            ],
            "user_5" => [
                "_data_source" => "user",
                "_data_structure" => [
                    "id" => ":id",
                    "name" => ":name"
                ]
            ],
        ]);

        $user = [
            'id' => 1,
            'name' => 'Hello User',
        ];

        $this->assertEquals($this->json->getDataSource(["_data_source" => "user"], []), $user);

        foreach ($array as $testuser) {
            $this->assertEquals($testuser, $user);
        }

    }

    public function testNestJson()
    {
        $user_post = [
            'id' => 1,
            'name' => 'Hello User',
            'post' => [
                'id' => 2,
                'name' => 'Hello post',
            ]
        ];
        $array = $this->json->getJson([
            "_data_source" => "user",
            "_data_structure" => [
                "id" => ":id",
                "name" => ":name",
                "post" => [
                    "_data_source" => "post",
                    "_data_structure" => [
                        "id" => ":id",
                        "name" => ":name"
                    ]
                ]
            ]
        ]);
        $this->assertEquals($array, $user_post);

        $array = $this->json->getJson([
            "_data_source" => "user",
            "_data_structure" => "user_post_structure"
        ]);

        $this->assertEquals($array, $user_post);

        $array = $this->json->getJson([
            "_data_source" => "user_post",
        ]);
        $this->assertEquals($array, $user_post);


            
    }

    public function testFunctionJson()
    {
        $userFunction = [
            'id' => 3,
            'name' => 'Hello user_function',
            "data_option" => []
        ];

        $array = $this->json->getJson([
            "_data_source" => "user_function",
        ]);

        $this->assertEquals($array, $userFunction);
    }

    public function testFunctionParamsJson()
    {
        $userFunction = [
            'id' => 3,
            'name' => 'Hello user_function',
            "data_option" => [
                "function_id" => 1
            ]
        ];

        $array = $this->json->getJson([
            "_data_source" => "user_function",
            "_data_option" => [
                "function_id" => 1
            ]
        ]);

        $this->assertEquals($array, $userFunction);
    }

    public function testFunctionDynamicParamsFromContextJson()
    {
        $userFunction = [
            'id' => 3,
            'name' => 'Hello user_function',
            "data_option" => [
                "function_id" => 1
            ]
        ];

        $array = $this->json->getJson([
            "_data_source" => "user_function",
            "_data_option" => [
                "function_id" => ":id"
            ],
            "_data_context" => [
                "id" => 1
            ]
        ]);

        $this->assertEquals($array, $userFunction);
    }

    public function testFunctionDynamicParamsFromContextUserJson()
    {
        $userFunction = [
            'id' => 3,
            'name' => 'Hello user_function',
            "data_option" => [
                "function_id" => 1
            ]
        ];

        $array = $this->json->getJson([
            "_data_source" => "user_function",
            "_data_option" => [
                "function_id" => ":id"
            ],
            "_data_context" => [
                "_data_source" => "user",
            ]
        ]);

        $this->assertEquals($array, $userFunction);
    }

    public function testFunctionNestJson()
    {   
        $v = [
            'id' => 10,
            'name' => 'Hello user_function_json',
            "data_option" => [],
            "post" => [
                'id' => 2,
                'name' => 'Hello post',
            ]
        ];

        $array = $this->json->getJson([
            "_data_source" => "user_function_json",
        ]);

        $this->assertEquals($array, $v);
    }

    public function testDataOption()
    {
        $option = [
            "user_id" => 1,
            "default_user_id" => 1
        ];

        $array = $this->json->getJson([
            "_data_source" => "user",
            "_data_structure" => [
                "id" => ":id",
                "name" => ":name",
                "user_function" => [
                    "_data_source" => "user_function",
                    "_data_option" => "user_option",
                ]
            ]
        ]);

        $this->assertEquals($array['user_function']['data_option'], $option);

        $option = [
            "user_id" => 2,
            "default_user_id" => 1
        ];

        $array = $this->json->getJson([
            "_data_source" => "user",
            "_data_structure" => [
                "id" => ":id",
                "name" => ":name",
                "user_function" => [
                    "_data_source" => "user_function",
                    "_data_option" => [
                        "user_id" => 2,
                        "default_user_id" => 1
                    ],
                ]
            ]
        ]);

        $this->assertEquals($array['user_function']['data_option'], $option);

        $option = [
            "user_id" => 3,
            "default_user_id" => 1
        ];

        $array = $this->json->getJson([
            "_data_source" => "user",
            "_data_structure" => [
                "id" => ":id",
                "name" => ":name",
                "user_function" => [
                    "_data_source" => "user_function",
                    "_data_option" => "user_option",
                    "_data_context" => [
                        "id" => 3
                    ]
                ]
            ]
        ]);

        $this->assertEquals($array['user_function']['data_option'], $option);

        $option = [
            "user_id" => 3,
            "default_user_id" => 1
        ];

        $array = $this->json->getJson([
            "_data_source" => "user",
            "_data_structure" => [
                "id" => ":id",
                "name" => ":name",
                "user_function" => [
                    "_data_source" => "user_function",
                    "_data_option" => [
                        "user_id" => ":id",
                        "default_user_id" => 1
                    ],
                    "_data_context" => [
                        "id" => 3,
                    ]
                ]
            ]
        ]);

        $this->assertEquals($array['user_function']['data_option'], $option);

    }

    public function testFunctionQuote()
    {

        $v = [
            'id' => 3,
            'name' => 'Hello user_function',
            "data_option" => [
                "user_id" => null,
                "default_user_id" => 1
            ]
        ];

        $array = $this->json->getJson([
            "_data_source" => "user_function_quote",
        ]);

        $this->assertEquals($array, $v);

        $v = [
            'id' => 3,
            'name' => 'Hello user_function',
            "data_option" => [
                "user_id" => 1,
                "default_user_id" => 1
            ]
        ];

        $array = $this->json->getJson([
            "_data_source" => "user_function_quote",
            "_data_context" => [
                "id" => 1
            ],
        ]);
        $this->assertEquals($array, $v);


        $v = [
            'id' => 3,
            'name' => 'Hello user_function',
            "data_option" => [
                "user_id" => 3,
                "default_user_id" => 1
            ]
        ];


        $array = $this->json->getJson([
            "_data_source" => "user_function_quote",
            "_data_context" => [
                "id" => 3
            ],
        ]);

        $this->assertEquals($array, $v);

        $v = [
            'id' => 3,
            'name' => 'Hello user_function',
            "data_option" => [
                "user_id" => 3,
                "default_user_id" => 1
            ]
        ];

        $array = $this->json->getJson([
            "_data_source" => "user_function_quote",
            "_data_context" => [
                "_data_source" => "comment",
            ],
        ]);
        $this->assertEquals($array, $v);

       

    }



    public function testJson()
    {
       
        $array = $this->json->getJson([
            "user" => ':user',
            "user_1" => [
                "_data_source" => "user",
            ],
            "user_2" => [
                "_data_source" => "user",
                "_data_structure" => "user"
            ],
            "user_structure" => [
                "_data_source" => "user",
                "_data_structure" => "user_structure"
            ],
            "user_5" => [
                "_data_source" => "user",
                "_data_structure" => [
                    "id" => ":id",
                    "name" => ":name"
                ]
            ],
            
            "user_id" => ":user.id",
            "user_function" => ':user_function',
            "user_option" => [
                "_data_source" => "user",
                "_data_structure" => [
                    "id" => ":id",
                    "name" => ":name",
                    "post" => [
                        "_data_source" => "user_function",
                        "_data_option" => "user_option",
                    ]
                ]

            ],
            "user_overwrite_option" => [
                "_data_source" => 'user',
                "_data_structure" => [
                    "id" => ":id",
                    "name" => ":name",
                    "post" => [
                        "_data_source" => "user_function",
                        "_data_option" => "user_option",
                        "_data_context" => [
                            "id" => "10"
                        ]
                    ]
                ]

            ],
            "user_function_quote" => [
                "_data_source" => 'user_function_quote',
                "_data_context" => [
                    "id" => "10"
                ],
            ],


            "post" => [
                "_data_source" => "post",
                "_data_structure" => "post"
            ],
            "post_structure" => [
                "_data_source" => "post",
                "_data_structure" => "post_structure"
            ],
            "post2" => [
                "_data_source" => "post",
                "_data_structure" => [
                    "id" => ":id",
                    "name" => ":name"
                ]
            ],
            "user_post" => ":user_post",
            "user_post" => [
                "_data_source" => "user_post",
                "_data_structure" => [
                    "id" => ":id",
                    "name" => ":name",
                    "post" => [
                        "_data_source" => ":post",
                        "_data_structure" => [
                            "id" => ":id",
                            "user_function_quote" => [
                                "_data_source" => 'user_function_quote',
                                "_data_context" => [
                                    "id" => "12"
                                ],
                            ],
                        ]
                    ]
                ]
            ],
        ]);

        $this->assertEquals($array, json_decode('{"user":{"id":1,"name":"Hello User"},"user_1":{"id":1,"name":"Hello User"},"user_2":{"id":1,"name":"Hello User"},"user_structure":{"id":1,"name":"Hello User"},"user_5":{"id":1,"name":"Hello User"},"user_id":1,"user_function":{"id":3,"name":"Hello user_function","data_option":[]},"user_option":{"id":1,"name":"Hello User","post":{"id":3,"name":"Hello user_function","data_option":{"user_id":1,"default_user_id":1}}},"user_overwrite_option":{"id":1,"name":"Hello User","post":{"id":3,"name":"Hello user_function","data_option":{"user_id":"10","default_user_id":1}}},"user_function_quote":{"id":3,"name":"Hello user_function","data_option":{"user_id":"10","default_user_id":1}},"post":{"id":2,"name":"Hello post"},"post_structure":{"id":2,"name":"Hello post"},"post2":{"id":2,"name":"Hello post"},"user_post":{"id":1,"name":"Hello User","post":{"id":2,"user_function_quote":{"id":3,"name":"Hello user_function","data_option":{"user_id":"12","default_user_id":1}}}}}', true));

    }

    public function testNestStructure()
    {
        $array = $this->json->getJson([
            "_data_source" => "user",
            "_data_structure" => [
                "_data_structure" => [
                    "id" => ":id",
                    "name" => ":name"
                ]
            ]
        ]);

        $this->assertEquals($array, [
            'id' => 1,
            'name' => 'Hello User',
        ]);

    }

    public function testReplaceString()
    {
        $array = $this->json->getJson([
            "_data_source" => "user",
            "_data_structure" => [
                "_data_structure" => [
                    "id" => "http://:id",
                    "name" => ":name"
                ]
            ]
        ]);

        $this->assertEquals($array, [
            'id' => "http://1",
            'name' => 'Hello User',
        ]);
    }

    public function testTwoColon()
    {
        $array = $this->json->getJson([
            "_data_source" => "user",
            "_data_structure" => [
                "_data_structure" => [
                    "id_name" => ":id :name",
                ]
            ]
        ]);

        $this->assertEquals($array, [
            'id_name' => '1 Hello User',
        ]);
    }

    public function testDataCOntext()
    {
        $array = $this->json->getJson([
            "_data_source" => ":user",
            "_data_context" => [
                "user" => [
                    "id" => 2,
                    "name" => "Hello User2",
                ],
            ]
        ]);

        $this->assertEquals($array, [
            'id' => 2,
            'name' => 'Hello User2',
        ]);
    }

    public function testDataSourceFunction()
    {
        $this->json->registerDataSource('data_source_function', function ($json, $config = []) {
            return [
                'id' => 3,
                'name' => 'Hello user_function',
            ];
        });

        $array = $this->json->getJson([
            "_data_source" => "data_source_function",
        ]);

        $this->assertEquals($array, [
            'id' => 3,
            'name' => 'Hello user_function',
        ]);



        $this->json->registerDataSource('data_source_function', function ($json, $config = []) {
            return [
                "_data_source" => "user",
            ];
        });
        $array = $this->json->getJson([
            "_data_source" => "data_source_function",
        ]);
        $this->assertEquals($array, [
            'id' => 1,
            'name' => 'Hello User',
        ]);


    }
}
