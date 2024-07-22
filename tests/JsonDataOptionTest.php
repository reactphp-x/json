<?php

namespace Reactphp\Framework\Json\Tests;

use Reactphp\Framework\Json\Json;



/**
 * :_data_option 只能在_data_option 和 _data_sturcture 中使用
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
            "_data_context" => [
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
            "_data_option" => [
                "name" => "工商银行"
            ],
            "_data_structure" => [
                "_is_support_array" => true,
                "name" => ":_data_option.name",
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
            "_data_context" => [
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
            "_data_option" => [
                "name" => "工商银行"
            ],
            "_data_structure" => [
                "_is_support_array" => true,
                "name" => [
                    "_data_structure" => ":_data_option.name"
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
            "_data_context" => [
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
            "_data_option" => [
                "name" => "工商银行"
            ],
            "_data_structure" => [
                "_is_support_array" => true,
                "name" => [
                    "_data_option" => [
                        "_data_option1" => ":_data_option",
                    ],
                    "_data_structure" => ":_data_option"
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
            "_data_context" => [
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
            "_data_option" => [
                "name" => "工商银行"
            ],
            "_data_structure" => [
                "_is_support_array" => true,
                "row" => [
                    "_data_option" => ":*",
                    "_data_structure" => [
                        "type" => ":_data_option.type",
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
            "_data_context" => [
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
            "_data_option" => [
                "name" => "工商银行"
            ],
            "_data_structure" => [
                "_is_support_array" => true,
                "row" => [
                    // "_data_option" => ":*",
                    "_data_structure" => ":*",
                    "_data_context" => [
                        "_data_option" => ":params",
                        "_data_context" => [
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
                        "_data_structure" => [
                            "_data_source" => ":data",
                            "_data_structure" => [
                                "_is_support_array" => true,
                                "code" => ":_data_option.code",
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
