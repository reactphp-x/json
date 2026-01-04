<?php

namespace ReactphpX\Json\Tests;

use ReactphpX\Json\Json;



class JsonComposeTest extends TestCase
{
    protected $json;

    public function setUp(): void
    {
        parent::setUp();
        $this->json = new Json();
        $this->json->registerDataSource('http', function ($json, $config) {
            $_data_option = $config['@option'] ?? [];
            $id = $_data_option['params']['id'] ?? 0;
            $apiDatas = [
                '1' => [
                    [
                        'id' => 1,
                        'name' => 'Hello User-1',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Hello User-2',
                    ],
                ],
                '3' => [
                    [
                        'id' => 3,
                        'name' => 'Hello User-3',
                    ],
                    [
                        'id' => 4,
                        'name' => 'Hello User-4',
                    ],
                    [
                        'id' => 5,
                        'name' => 'Hello User-5',
                    ],
                ],
            ];
            return $apiDatas[$id] ?? [];
        });

        $this->json->registerDataSource('transform', function ($json, $config) {
            $_data_option = $config['@option'] ?? [];
            $data = $_data_option['data'] ?? [];
            return $json->getJson([
                "@source" => ":*",
                "@context" => $data,
                "@structure" => [
                    "@is_array" => true,
                    "id" => ":id",
                    "name" => ":name",
                ]
            ]);
        });

        $this->json->registerDataSource('insertDatabase', function ($json, $config) {
            $_data_option = $config['@option'] ?? [];
            $table = $_data_option['table'] ?? '';
            $data = $_data_option['data'] ?? [];
            return count($data);
        });

        $this->json->registerDataSource('configs', function ($json, $config) {
            return [
                [
                    "table" => "xxxxx",
                    "params" => [
                        "id" => 1
                    ]
                ],
                [
                    "table" => "xxxxx",
                    "params" => [
                        "id" => 3
                    ]
                ],
            ];
        });
    }

    public function testHttp()
    {

        $array = $this->json->getJson([
            "@source" => "http",
            "@option" => [
                "params" => [
                    "id" => 1
                ]
            ],
        ]);

        $this->assertEquals($array, [
            [
                'id' => 1,
                'name' => 'Hello User-1',
            ],
            [
                'id' => 2,
                'name' => 'Hello User-2',
            ],
        ]);

        $array = $this->json->getJson([
            "@source" => "http",
            "@option" => [
                "params" => [
                    "id" => 3
                ]
            ],
        ]);

        $this->assertEquals($array, [
            [
                'id' => 3,
                'name' => 'Hello User-3',
            ],
            [
                'id' => 4,
                'name' => 'Hello User-4',
            ],
            [
                'id' => 5,
                'name' => 'Hello User-5',
            ],
        ]);
    }

    public function testTransform()
    {
        $array = $this->json->getJson([
            "@source" => "transform",
            "@option" => [
                "data" => ":*"
            ],
            "@context" => [
                "@source" => "http",
                "@option" => [
                    "params" => [
                        "id" => 1
                    ]
                ],
            ]
        ]);

        $this->assertEquals($array, [
            [
                'id' => 1,
                'name' => 'Hello User-1',
            ],
            [
                'id' => 2,
                'name' => 'Hello User-2',
            ],
        ]);

        $array = $this->json->getJson([
            "@source" => "transform",
            "@option" => [
                "data" => ":*"
            ],
            "@context" => [
                "@source" => "http",
                "@option" => [
                    "params" => [
                        "id" => 3
                    ]
                ],
            ]
        ]);

        $this->assertEquals($array, [
            [
                'id' => 3,
                'name' => 'Hello User-3',
            ],
            [
                'id' => 4,
                'name' => 'Hello User-4',
            ],
            [
                'id' => 5,
                'name' => 'Hello User-5',
            ],
        ]);
    }

    public function testInsertDatabase()
    {
        $row = $this->json->getJson([
            "@source" => "insertDatabase",
            "@option" => [
                "table" => "xxxxx",
                "data" => [
                    [
                        'id' => 1,
                        'name' => 'Hello User-1',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Hello User-2',
                    ],
                ]
            ],
        ]);

        $this->assertTrue($row === 2);

        $row = $this->json->getJson([
            "@source" => "insertDatabase",
            "@option" => [
                "table" => "xxxxx",
                "data" => [
                    [
                        'id' => 3,
                        'name' => 'Hello User-3',
                    ],
                    [
                        'id' => 4,
                        'name' => 'Hello User-4',
                    ],
                    [
                        'id' => 5,
                        'name' => 'Hello User-5',
                    ],
                ]
            ],
        ]);

        $this->assertTrue($row === 3);
    }

    public function testConfigs()
    {
        $array = $this->json->getJson([
            "@source" => "configs",
        ]);

        $this->assertEquals($array, [
            [
                "table" => "xxxxx",
                "params" => [
                    "id" => 1
                ]
            ],
            [
                "table" => "xxxxx",
                "params" => [
                    "id" => 3
                ]
            ],
        ]);
    }

    public function testAll()
    {
        // 自上而下
        $array = $this->json->getJson([
            "@source" => "configs",
            "@structure" => [
                "@is_array" => true,
                "row" => [
                    "@source" => "insertDatabase",
                    "@option" => [
                        "table" => ":table",
                        "data" => ":data",
                    ],
                    "@context" => [
                        "table" => ":table",
                        "data" => [
                            "@source" => "transform",
                            "@option" => [
                                "data" => ":*"
                            ],
                            "@context" => [
                                "@source" => "http",
                                "@option" => [
                                    "params" => ":params",
                                    "test" => 'test'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals($array, [
            [
                "row" => 2
            ],
            [
                "row" => 3
            ],
        ]);

    }

    public function testAbstractHttpAndtransform()
    {
        $this->json->registerDataSource('fetch_and_transform', function ($json, $config) {
            return [
                "@source" => "transform",
                "@option" => [
                    "data" => ":*"
                ],
                "@context" => [
                    "@source" => "http",
                    "@option" => [
                        "params" => ":params"
                    ]
                ]
            ];
        });

        $array = $this->json->getJson([
            "@source" => "configs",
            "@structure" => [
                "@is_array" => true,
                "row" => [
                    "@source" => "insertDatabase",
                    "@option" => [
                        "table" => ":table",
                        "data" => ":data",
                    ],
                    "@context" => [
                        "table" => ":table",
                        "data" => [
                            "@source" => "fetch_and_transform",
                            "@context" => [
                                "params" => ":params",
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals($array, [
            [
                "row" => 2
            ],
            [
                "row" => 3
            ],
        ]);
    }

    public function testAbstractInsertDatabase()
    {
        $this->json->registerDataSource('fetch_and_transform', function ($json, $config) {
            return [
                "@source" => "transform",
                "@option" => [
                    "data" => ":*"
                ],
                "@context" => [
                    "@source" => "http",
                    "@option" => [
                        "params" => ":params"
                    ]
                ]
            ];
        });

        $this->json->registerDataSource('insert_database_by_api', function ($json, $config) {
            return [
                "@source" => "insertDatabase",
                "@option" => [
                    "table" => ":table",
                    "data" => ":data",
                ],
                "@context" => [
                    "table" => ":table",
                    "data" => [
                        "@source" => "fetch_and_transform",
                        "@context" => [
                            "params" => ":params",
                        ]
                    ]
                ]
            ];
        });

        $array = $this->json->getJson([
            "@source" => "configs",
            "@structure" => [
                "@is_array" => true,
                "row" => [
                    "@source" => "insert_database_by_api"
                ]
            ]
        ]);

        $this->assertEquals($array, [
            [
                "row" => 2
            ],
            [
                "row" => 3
            ],
        ]);

        $array = $this->json->getJson([
            "@source" => [
                [
                    "table" => "xxxxx",
                    "params" => [
                        "id" => 1
                    ]
                ]
            ],
            "@structure" => [
                "@is_array" => true,
                "row" => [
                    "@source" => "insert_database_by_api"
                ]
            ]
        ]);

        $this->assertEquals($array, [
            [
                "row" => 2
            ],
        ]);
        $array = $this->json->getJson([
            "@source" => [
                [
                    "table" => "xxxxx",
                    "params" => [
                        "id" => 3
                    ]
                ]
            ],
            "@structure" => [
                "@is_array" => true,
                "row" => [
                    "@source" => "insert_database_by_api"
                ]
            ]
        ]);

        $this->assertEquals($array, [
            [
                "row" => 3
            ],
        ]);
    }

    public function testSomeStructure()
    {
        // return ;

        $this->json->registerDataSource('fetch_and_transform', function ($json, $config) {
            return [
                "@source" => "transform",
                "@option" => [
                    "data" => ":*"
                ],
                "@context" => [
                    "@source" => "http",
                    "@option" => [
                        "params" => ":params"
                    ]
                ]
            ];
        });

       $array = $this->json->getJson([
            "@source" => "configs",
            "@structure" => [
                "@is_array" => true,
                "row" => [
                    "@context" => [
                        "table1" => ":table",
                        "data" => [
                            "@source" => "fetch_and_transform",
                            "@context" => [
                                "params" => ":params",
                            ]
                        ]
                    ],
                    "@structure" => [
                        "table" => ":table1",
                        "data" => ":data.*.id",
                    ]
                ]
            ]
        ]);

        $this->assertEquals([
            [
                "row" => [
                    "table" => "xxxxx",
                    "data" => [
                        1,
                        2,
                    ],
                ],
            ],
            [
                "row" => [
                    "table" => "xxxxx",
                    "data" => [
                        3,
                        4,
                        5,
                    ],
                ],
            ],

            
        ], $array);


        $array = $this->json->getJson([
            "@source" => "configs",
            "@structure" => [
                "@is_array" => true,
                "row" => [
                    "@structure" => [
                        "table" => ":table",
                        "data" => ":params.id",
                        "row" => [
                            // "@source" => "fetch_and_transform",
                            "@context" => [
                                "params" => ":params",
                            ],
                            "@structure" => [
                                "params" => ":params",
                                "id" => ":params.id",
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals([
            [
                "row" => [
                    "table" => "xxxxx",
                    "data" => 1,
                    "row" => [
                        "params" => [
                            "id" => 1
                        ],
                        "id" => 1
                    ]
                ],
            ],
            [
                "row" => [
                    "table" => "xxxxx",
                    "data" => 3,
                    "row" => [
                        "params" => [
                            "id" => 3
                        ],
                        "id" => 3
                    ]
                ],
            ],
        ], $array);


        $array = $this->json->getJson([
            "@source" => "configs",
            "@structure" => [
                "@is_array" => true,
                "row" => [
                    "@structure" => [
                        "table" => ":table",
                        "data" => ":params.id",
                        "row" => [
                            "@source" => "fetch_and_transform",
                            "@context" => [
                                "params" => ":params",
                            ],
                            "@structure" => [
                                "name" => ":*.name",
                                "id" => ":*.id",
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals([
            [
                "row" => [
                    "table" => "xxxxx",
                    "data" => 1,
                    "row" => [
                        "name" => [
                            0 => "Hello User-1",
                            1 => "Hello User-2",
                        ],
                        "id" => [
                            0 => 1,
                            1 => 2,
                        ]
                    ]
                ],
            ],
            [
                "row" => [
                    "table" => "xxxxx",
                    "data" => 3,
                    "row" => [
                       "name" => [
                            0 => "Hello User-3",
                            1 => "Hello User-4",
                            2 => "Hello User-5",
                       ],
                        "id" => [
                            0 => 3,
                            1 => 4,
                            2 => 5,
                        ]
                    ]
                ],
            ],
        ], $array);

        $array = $this->json->getJson([
            "@source" => "configs",
            "@structure" => [
                "@is_array" => true,
                "row" => [
                    "@structure" => [
                        "table" => ":table",
                        "data" => ":params.id",
                        "row" => [
                            "@source" => "fetch_and_transform",
                            "@context" => [
                                "params" => ":params",
                            ],
                            "@structure" => [
                                "@is_array" => true,

                                "name" => ":name",
                                "id" => ":id",
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals([
            [
                "row" => [
                    "table" => "xxxxx",
                    "data" => 1,
                    "row" => [
                        [
                            "name" => "Hello User-1",
                            "id" => 1,
                        ],
                        [
                            "name" => "Hello User-2",
                            "id" => 2,
                        ]
                    ]
                ],
            ],
            [
                "row" => [
                    "table" => "xxxxx",
                    "data" => 3,
                    "row" => [
                        [
                            "name" => "Hello User-3",
                            "id" => 3,
                        ],
                        [
                            "name" => "Hello User-4",
                            "id" => 4,
                        ],
                        [
                            "name" => "Hello User-5",
                            "id" => 5,
                        ]
                      ]

                ],
            ],
        ], $array);


        $array = $this->json->getJson([
            "@source" => "configs",
            "@structure" => [
                "@is_array" => true,
                "row" => [
                    "@structure" => [
                        "table" => ":table",
                        "data" => ":params.id",
                        "row" => [
                            "@source" => "fetch_and_transform",
                            "@context" => [
                                "params" => ":params",
                            ],
                            "@structure" => [
                                "@is_array" => true,
                                [
                                    "name" => ":name",
                                    "id" => ":id",
                                ]
                               
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        
        $this->assertEquals([
            [
                "row" => [
                    "table" => "xxxxx",
                    "data" => 1,
                    "row" => [
                        [[
                            "name" => "Hello User-1",
                            "id" => 1,
                        ]],
                        [[
                            "name" => "Hello User-2",
                            "id" => 2,
                        ]]
                    ]
                ],
            ],
            [
                "row" => [
                    "table" => "xxxxx",
                    "data" => 3,
                    "row" => [
                        [[
                            "name" => "Hello User-3",
                            "id" => 3,
                        ]],
                        [[
                            "name" => "Hello User-4",
                            "id" => 4,
                        ]],
                        [[
                            "name" => "Hello User-5",
                            "id" => 5,
                        ]]
                      ]

                ],
            ],
        ], $array);
    }
}
