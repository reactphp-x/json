<?php

namespace ReactphpX\Json\Tests;

use ReactphpX\Json\Json;



class JsonComposeDownToUpTest extends TestCase
{
    protected $json;

    public function setUp(): void
    {
        parent::setUp();
        $this->json = new Json();
        $this->json->registerDataSource('http', function ($json, $config) {
            $_data_option = $config['_data_option'] ?? [];
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
            $apiData = $apiDatas[$id] ?? [];
            // dd($id, $apiData,333333);

            return $apiData;
        });

        $this->json->registerDataSource('transform', function ($json, $config) {
            $_data_option = $config['_data_option'] ?? [];
            $data = $_data_option['data'] ?? [];
            // dd($config, $data);

            return $json->getJson([
                "_data_source" => ":*",
                "_data_context" => $data,
                "_data_structure" => [
                    "_is_support_array" => true,
                    "id" => ":id",
                    "name" => ":name",
                ]
            ]);
        });

        $this->json->registerDataSource('insertDatabase', function ($json, $config) {
            $_data_option = $config['_data_option'] ?? [];
            $table = $_data_option['table'] ?? '';
            $data = $_data_option['data'] ?? [];
            // dd($config, $data);
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

    public function testAll()
    {
        // return;
        // 自下而上
        $array = $this->json->getJson([
            "_data_source" => "configs",
            "_data_structure" => [
                "_is_support_array" => true,
                "row" => [
                    "_data_context" => [
                        "config" => ":*",
                        "http_data" => [
                            "_data_source" => "http",
                            "_data_option" => [
                                "params" => ":params"
                            ],
                        ]
                    ],
                    "_data_structure" => [
                        "_data_context" => [
                            "config" => ":config",
                            "transform_data" => [
                                "_data_source" => "transform",
                                "_data_option" => [
                                    "data" => ":http_data"
                                ]
                            ]
                        ],
                        "_data_structure" => [
                            "_data_context" => [
                                "_data_source" => "insertDatabase",
                                "_data_option" => [
                                    "table" => ":config.table",
                                    "data" => ":transform_data",
                                ],
                            ],
                            "_data_structure" => ":*"

                        ]
                    ]
                ]
            ]
        ]);
        // dd($array);
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
        // return;
        $this->json->registerDataStructure('fetch_and_transform', function ($json, $config) {
            return [
                "_data_context" => [
                    "config" => ":*",
                    "http_data" => [
                        "_data_source" => "http",
                        "_data_option" => [
                            "params" => ":params"
                        ],
                    ]
                ],
                "_data_structure" => [
                    "_data_context" => [
                        "config" => ":config",
                        "transform_data" => [
                            "_data_source" => "transform",
                            "_data_option" => [
                                "data" => ":http_data"
                            ]
                        ]
                    ],
                ]
            ];
        });

        $array = $this->json->getJson([
            "_data_source" => "configs",
            "_data_structure" => [
                "_is_support_array" => true,
                "row" => [
                    "_data_context" => [
                        "_data_structure" => "fetch_and_transform",
                    ],
                    "_data_structure" => [
                        "_data_context" => [
                            "_data_source" => "insertDatabase",
                            "_data_option" => [
                                "table" => ":config.table",
                                "data" => ":transform_data",
                            ],
                        ],
                        "_data_structure" => ":*"

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
        // return ;
        $this->json->registerDataStructure('fetch_and_transform', function ($json, $config) {
            return [
                "_data_context" => [
                    "config" => ":*",
                    "http_data" => [
                        "_data_source" => "http",
                        "_data_option" => [
                            "params" => ":params"
                        ],
                    ]
                ],
                "_data_structure" => [
                    "_data_context" => [
                        "config" => ":config",
                        "transform_data" => [
                            "_data_source" => "transform",
                            "_data_option" => [
                                "data" => ":http_data"
                            ]
                        ]
                    ],
                ]
            ];
        });
        $this->json->registerDataSource('fetch_and_transform', function ($json, $config) {
            return [
                "_data_context" => [
                    "config" => ":*",
                    "http_data" => [
                        "_data_source" => "http",
                        "_data_option" => [
                            "params" => ":params"
                        ],
                    ]
                ],
                "_data_structure" => [
                    "_data_context" => [
                        "config" => ":config",
                        "transform_data" => [
                            "_data_source" => "transform",
                            "_data_option" => [
                                "data" => ":http_data"
                            ]
                        ]
                    ],
                ]
            ];
        });

        $this->json->registerDataStructure('insert_database_by_api', function ($json, $config) {
            return [
                "_data_context" => [
                    "_data_structure" => "fetch_and_transform",
                ],
                "_data_structure" => [
                    "_data_context" => [
                        "_data_source" => "insertDatabase",
                        "_data_option" => [
                            "table" => ":config.table",
                            "data" => ":transform_data",
                        ],
                    ],
                    "_data_structure" => ":*"
                ]
            ];
        });

        $this->json->registerDataSource('insert_database_by_api', function ($json, $config) {
            return [
                "_data_context" => [
                    "_data_source" => "fetch_and_transform",
                ],
                "_data_structure" => [
                    "_data_context" => [
                        "_data_source" => "insertDatabase",
                        "_data_option" => [
                            "table" => ":config.table",
                            "data" => ":transform_data",
                        ],
                    ],
                    "_data_structure" => ":*"
                ]
            ];
        });

        $array = $this->json->getJson([
            "_data_source" => "configs",
            "_data_structure" => [
                "_is_support_array" => true,
                "row" => [
                    "_data_context" => ":*",
                    "_data_structure" => "insert_database_by_api"
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
            "_data_source" => "configs",
            "_data_structure" => [
                "_is_support_array" => true,
                "row" => [
                    "_data_source" => "insert_database_by_api"
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
            "_data_source" => [
                "_data_source" => "configs",
                "_data_structure" => [
                    "_is_support_array" => true,
                    "row" => [
                        "_data_structure" => "insert_database_by_api"
                    ]
                ]
            ],
            "_data_structure" => ':*.row'
        ]);

        // dd($array);

        $this->assertEquals($array, [
           2,3
        ]);

        $array = $this->json->getJson([
            "_data_context" => [
                "_data_source" => "configs",
                "_data_structure" => [
                    "_is_support_array" => true,
                    "row" => [
                        "_data_structure" => "insert_database_by_api"
                    ]
                ]
            ],
            
            "_data_structure" => ':*.row'
        ]);

        $this->assertEquals($array, [
           2,3
        ]);
        $array = $this->json->getJson([
            "_data_context" => [
                "_data_source" => "configs",
                "_data_structure" => [
                    "_is_support_array" => true,
                    "row" => [
                        "_data_structure" => "insert_database_by_api"
                    ]
                ]
            ],
            "_data_source" => ":*.row",
            "_data_structure" => ':*'
        ]);

        $this->assertEquals($array, [
           2,3
        ]);
    }

    public function testDataSourceContext()
    {
        $array = $this->json->getJson([
            "_data_source" => [
                "_data_source" => ":http_data.*.id",
                "_data_context" => [
                    "id" => 1,
                    "http_data" => [
                        "_data_source" => "http",
                        "_data_option" => [
                            "params" => [
                                "id" => 1
                            ]
                        ],
                    ]
                ],
                "_data_structure" => ':*'
            ],
        ]);

        $this->assertEquals($array, [
            1,2
        ]);

        $array = $this->json->getJson([
            "_data_source" => [
                [
                    "_data_source" => ":http_data.*.id",
                    "_data_context" => [
                        "id" => 1,
                        "http_data" => [
                            "_data_source" => "http",
                            "_data_option" => [
                                "params" => [
                                    "id" => 1
                                ]
                            ],
                        ]
                    ],
                    "_data_structure" => ':*'
                ],
                [
                    "_data_context" => [
                        "id" => 1,
                        "http_data" => [
                            "_data_source" => "http",
                            "_data_option" => [
                                "params" => [
                                    "id" => 1
                                ]
                            ],
                        ]
                    ], 
                    "_data_structure" => ':id'

                ],
                [
                    "_data_context" => [
                        "id" => 1,
                        "http_data" => [
                            "_data_source" => "http",
                            "_data_option" => [
                                "params" => [
                                    "id" => 1
                                ]
                            ],
                            "_data_structure" => ':*.name'

                        ]
                    ], 
                    "_data_structure" => ':http_data'

                ]
            ],
            "_data_structure" => [
                ":*",
                [
                    "_data_context" => [
                        "id" => 1,
                        "http_data" => [
                            "_data_source" => "http",
                            "_data_option" => [
                                "params" => [
                                    "id" => 1
                                ]
                            ],
                        ]
                    ], 
                    "_data_structure" => ':http_data.*.id'

                ]
            ]
        ]);

        $this->assertEquals($array, [
            [
                [
                    1,2
                ],
                1,
                [
                    "Hello User-1",
                    "Hello User-2"
                ]
            ],
            [
                1,2
            ],
        ]);

        $array = $this->json->getJson([
            "_data_source" => "configs",
            "_data_structure" => [
                "_is_support_array" => true,
                "id" => ":params.id",
                [
                    "_data_source" => "http",
                    "_data_option" => [
                        "params" => [
                            "id" => ":params.id"
                        ]
                    ],
                    "_data_structure" => ":*.id"
                ]
            ]
        ]);

        $this->assertEquals($array, [
            [
                "id" => 1,
                [
                    1,2
                ]
            ],
            [
                "id" => 3,
                [
                    3,4,5
                ]
            ]
        ]);

        $array = $this->json->getJson([
            "_data_source" => "configs",
            "_data_structure" => [
                "_is_support_array" => true,
                "id" => ":params.id",
                [
                    "_data_context" => [
                        "http_data" => [
                            "_data_source" => "http",
                            "_data_option" => [
                                "params" => [
                                    "id" => ":params.id"
                                ]
                            ],
                        ]
                    ],
                   
                    "_data_structure" => ":http_data.*.id"
                ]
            ]
        ]);

        $this->assertEquals($array, [
            [
                "id" => 1,
                [
                    1,2
                ]
            ],
            [
                "id" => 3,
                [
                    3,4,5
                ]
            ]
        ]);

    }
}