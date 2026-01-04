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
            $apiData = $apiDatas[$id] ?? [];
            // dd($id, $apiData,333333);

            return $apiData;
        });

        $this->json->registerDataSource('transform', function ($json, $config) {
            $_data_option = $config['@option'] ?? [];
            $data = $_data_option['data'] ?? [];
            // dd($config, $data);

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
            "@source" => "configs",
            "@structure" => [
                "@is_array" => true,
                "row" => [
                    "@context" => [
                        "config" => ":*",
                        "http_data" => [
                            "@source" => "http",
                            "@option" => [
                                "params" => ":params"
                            ],
                        ]
                    ],
                    "@structure" => [
                        "@context" => [
                            "config" => ":config",
                            "transform_data" => [
                                "@source" => "transform",
                                "@option" => [
                                    "data" => ":http_data"
                                ]
                            ]
                        ],
                        "@structure" => [
                            "@context" => [
                                "@source" => "insertDatabase",
                                "@option" => [
                                    "table" => ":config.table",
                                    "data" => ":transform_data",
                                ],
                            ],
                            "@structure" => ":*"

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
                "@context" => [
                    "config" => ":*",
                    "http_data" => [
                        "@source" => "http",
                        "@option" => [
                            "params" => ":params"
                        ],
                    ]
                ],
                "@structure" => [
                    "@context" => [
                        "config" => ":config",
                        "transform_data" => [
                            "@source" => "transform",
                            "@option" => [
                                "data" => ":http_data"
                            ]
                        ]
                    ],
                ]
            ];
        });

        $array = $this->json->getJson([
            "@source" => "configs",
            "@structure" => [
                "@is_array" => true,
                "row" => [
                    "@context" => [
                        "@structure" => "fetch_and_transform",
                    ],
                    "@structure" => [
                        "@context" => [
                            "@source" => "insertDatabase",
                            "@option" => [
                                "table" => ":config.table",
                                "data" => ":transform_data",
                            ],
                        ],
                        "@structure" => ":*"

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
                "@context" => [
                    "config" => ":*",
                    "http_data" => [
                        "@source" => "http",
                        "@option" => [
                            "params" => ":params"
                        ],
                    ]
                ],
                "@structure" => [
                    "@context" => [
                        "config" => ":config",
                        "transform_data" => [
                            "@source" => "transform",
                            "@option" => [
                                "data" => ":http_data"
                            ]
                        ]
                    ],
                ]
            ];
        });
        $this->json->registerDataSource('fetch_and_transform', function ($json, $config) {
            return [
                "@context" => [
                    "config" => ":*",
                    "http_data" => [
                        "@source" => "http",
                        "@option" => [
                            "params" => ":params"
                        ],
                    ]
                ],
                "@structure" => [
                    "@context" => [
                        "config" => ":config",
                        "transform_data" => [
                            "@source" => "transform",
                            "@option" => [
                                "data" => ":http_data"
                            ]
                        ]
                    ],
                ]
            ];
        });

        $this->json->registerDataStructure('insert_database_by_api', function ($json, $config) {
            return [
                "@context" => [
                    "@structure" => "fetch_and_transform",
                ],
                "@structure" => [
                    "@context" => [
                        "@source" => "insertDatabase",
                        "@option" => [
                            "table" => ":config.table",
                            "data" => ":transform_data",
                        ],
                    ],
                    "@structure" => ":*"
                ]
            ];
        });

        $this->json->registerDataSource('insert_database_by_api', function ($json, $config) {
            return [
                "@context" => [
                    "@source" => "fetch_and_transform",
                ],
                "@structure" => [
                    "@context" => [
                        "@source" => "insertDatabase",
                        "@option" => [
                            "table" => ":config.table",
                            "data" => ":transform_data",
                        ],
                    ],
                    "@structure" => ":*"
                ]
            ];
        });

        $array = $this->json->getJson([
            "@source" => "configs",
            "@structure" => [
                "@is_array" => true,
                "row" => [
                    "@context" => ":*",
                    "@structure" => "insert_database_by_api"
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
                "@source" => "configs",
                "@structure" => [
                    "@is_array" => true,
                    "row" => [
                        "@structure" => "insert_database_by_api"
                    ]
                ]
            ],
            "@structure" => ':*.row'
        ]);

        // dd($array);

        $this->assertEquals($array, [
           2,3
        ]);

        $array = $this->json->getJson([
            "@context" => [
                "@source" => "configs",
                "@structure" => [
                    "@is_array" => true,
                    "row" => [
                        "@structure" => "insert_database_by_api"
                    ]
                ]
            ],
            
            "@structure" => ':*.row'
        ]);

        $this->assertEquals($array, [
           2,3
        ]);
        $array = $this->json->getJson([
            "@context" => [
                "@source" => "configs",
                "@structure" => [
                    "@is_array" => true,
                    "row" => [
                        "@structure" => "insert_database_by_api"
                    ]
                ]
            ],
            "@source" => ":*.row",
            "@structure" => ':*'
        ]);

        $this->assertEquals($array, [
           2,3
        ]);
    }

    public function testDataSourceContext()
    {
        $array = $this->json->getJson([
            "@source" => [
                "@source" => ":http_data.*.id",
                "@context" => [
                    "id" => 1,
                    "http_data" => [
                        "@source" => "http",
                        "@option" => [
                            "params" => [
                                "id" => 1
                            ]
                        ],
                    ]
                ],
                "@structure" => ':*'
            ],
        ]);

        $this->assertEquals($array, [
            1,2
        ]);

        $array = $this->json->getJson([
            "@source" => [
                [
                    "@source" => ":http_data.*.id",
                    "@context" => [
                        "id" => 1,
                        "http_data" => [
                            "@source" => "http",
                            "@option" => [
                                "params" => [
                                    "id" => 1
                                ]
                            ],
                        ]
                    ],
                    "@structure" => ':*'
                ],
                [
                    "@context" => [
                        "id" => 1,
                        "http_data" => [
                            "@source" => "http",
                            "@option" => [
                                "params" => [
                                    "id" => 1
                                ]
                            ],
                        ]
                    ], 
                    "@structure" => ':id'

                ],
                [
                    "@context" => [
                        "id" => 1,
                        "http_data" => [
                            "@source" => "http",
                            "@option" => [
                                "params" => [
                                    "id" => 1
                                ]
                            ],
                            "@structure" => ':*.name'

                        ]
                    ], 
                    "@structure" => ':http_data'

                ]
            ],
            "@structure" => [
                ":*",
                [
                    "@context" => [
                        "id" => 1,
                        "http_data" => [
                            "@source" => "http",
                            "@option" => [
                                "params" => [
                                    "id" => 1
                                ]
                            ],
                        ]
                    ], 
                    "@structure" => ':http_data.*.id'

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
            "@source" => "configs",
            "@structure" => [
                "@is_array" => true,
                "id" => ":params.id",
                [
                    "@source" => "http",
                    "@option" => [
                        "params" => [
                            "id" => ":params.id"
                        ]
                    ],
                    "@structure" => ":*.id"
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
            "@source" => "configs",
            "@structure" => [
                "@is_array" => true,
                "id" => ":params.id",
                [
                    "@context" => [
                        "http_data" => [
                            "@source" => "http",
                            "@option" => [
                                "params" => [
                                    "id" => ":params.id"
                                ]
                            ],
                        ]
                    ],
                   
                    "@structure" => ":http_data.*.id"
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