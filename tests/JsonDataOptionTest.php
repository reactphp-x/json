<?php

namespace ReactphpX\Json\Tests;

use ReactphpX\Json\Json;



/**
 * :@option 只能在@option 和 @structure 中使用
 */
class JsonDataOptionTest extends TestCase
{
    protected $json;

    public function setUp(): void
    {
        parent::setUp();
        $this->json = new Json();
    }

    public function testDataOptionParamsToStructure()
    {


        $array = $this->json->getJson([
            "@context" => [
                [
                    "code" => "601398",
                    "structure_key" => "mairui_gupiao_fsjys",
                    "type" => "5m"
                ],
                [
                    "code" => "601398",
                    "structure_key" => "mairui_gupiao_fsjys",
                    "type" => "15m"
                ]
            ],
            "@option" => [
                "name" => "工商银行"
            ],
            "@structure" => [
                "@is_array" => true,
                "name" => ":@option.name",
                "code" => ":code"
            ]

        ]);

        $this->assertEquals([
            [
                "name" => "工商银行",
                "code" => "601398"
            ],
            [
                "name" => "工商银行",
                "code" => "601398"
            ]
        ], $array);

        $array = $this->json->getJson([
            "@context" => [
                [
                    "code" => "601398",
                    "structure_key" => "mairui_gupiao_fsjys",
                    "type" => "5m"
                ],
                [
                    "code" => "601398",
                    "structure_key" => "mairui_gupiao_fsjys",
                    "type" => "15m"
                ]
            ],
            "@option" => [
                "name" => "工商银行"
            ],
            "@structure" => [
                "@is_array" => true,
                "name" => [
                    "@structure" => ":@option.name"
                ],
                "code" => ":code"
            ]

        ]);

        $this->assertEquals([
            [
                "name" => "工商银行",
                "code" => "601398"
            ],
            [
                "name" => "工商银行",
                "code" => "601398"
            ]
        ], $array);

        $array = $this->json->getJson([
            "@context" => [
                [
                    "code" => "601398",
                    "structure_key" => "mairui_gupiao_fsjys",
                    "type" => "5m"
                ],
                [
                    "code" => "601398",
                    "structure_key" => "mairui_gupiao_fsjys",
                    "type" => "15m"
                ]
            ],
            "@option" => [
                "name" => "工商银行"
            ],
            "@structure" => [
                "@is_array" => true,
                "name" => [
                    "@option" => [
                        "_data_option1" => ":@option",
                    ],
                    "@structure" => ":@option"
                ],
                "code" => ":code"
            ]

        ]);

        $this->assertEquals([
            [
                "name" => [
                    "_data_option1" => [
                        "name" => "工商银行"
                    ]

                ],
                "code" => "601398"
            ],
            [
                "name" => [

                    "_data_option1" => [
                        "name" => "工商银行"
                    ]

                ],
                "code" => "601398"
            ]
        ], $array);
        // dd($array);


        $array = $this->json->getJson([
            "@context" => [
                [
                    "code" => "601398",
                    "structure_key" => "mairui_gupiao_fsjys",
                    "type" => "5m"
                ],
                [
                    "code" => "601398",
                    "structure_key" => "mairui_gupiao_fsjys",
                    "type" => "15m"
                ]
            ],
            "@option" => [
                "name" => "工商银行"
            ],
            "@structure" => [
                "@is_array" => true,
                "row" => [
                    "@option" => ":*",
                    "@structure" => [
                        "type" => ":@option.type",
                        "code" => ":code"
                    ]
                ],
                "code" => ":code"
            ]

        ]);

        $this->assertEquals([
            [
                "row" => [
                    "type" => "5m",
                    "code" => "601398"
                ],
                "code" => "601398"
            ],
            [
                "row" => [
                    "type" => "15m",
                    "code" => "601398"
                ],
                "code" => "601398"
            ]

        ], $array);


        $array = $this->json->getJson([
            "@context" => [
                [
                    "code" => "601398",
                    "structure_key" => "mairui_gupiao_fsjys",
                    "type" => "5m"
                ],
                [
                    "code" => "601398",
                    "structure_key" => "mairui_gupiao_fsjys",
                    "type" => "15m"
                ]
            ],
            "@option" => [
                "name" => "工商银行"
            ],
            "@structure" => [
                "@is_array" => true,
                "row" => [
                    // "@option" => ":*",
                    "@structure" => ":*",
                    "@context" => [
                        "@option" => ":params",
                        "@context" => [
                            "params" => ":*",
                            "data" => [
                                [
                                    "id" => 1,
                                    "name" => "Hello User-1",
                                ],
                                [
                                    "id" => 2,
                                    "name" => "Hello User-2",
                                ]
                            ],
                        ],
                        "@structure" => [
                            "@source" => ":data",
                            "@structure" => [
                                "@is_array" => true,
                                "code" => ":@option.code",
                                "id" => ":id",
                                "name" => ":name",
                            ]
                        ]
                    ]
                ],
                "code" => ":code"
            ]

        ]);

        $this->assertEquals([
            [
                "row" => [
                    [
                        "code" => "601398",
                        "id" => 1,
                        "name" => "Hello User-1",
                    ],
                    [
                        "code" => "601398",
                        "id" => 2,
                        "name" => "Hello User-2",
                    ]
                ],
                "code" => "601398"
            ],
            [
                "row" => [
                    [
                        "code" => "601398",
                        "id" => 1,
                        "name" => "Hello User-1",
                    ],
                    [
                        "code" => "601398",
                        "id" => 2,
                        "name" => "Hello User-2",
                    ]
                ],
                "code" => "601398"
            ]

        ], $array);
    }
}
