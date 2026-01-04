<?php

namespace ReactphpX\Json\Tests;

use ReactphpX\Json\Json;



class JsonLogicTest extends TestCase
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

        // logic


        $this->json->registerDataSource('user_logic1', function ($json, $config) {

            return $this->_logic($json, $config, function () {
                return [
                    'id' => 1,
                    'name' => 'Hello user_logic1',
                ];
            });
        });

        $this->json->registerDataSource('user_logic2', function ($json, $config) {

            return $this->_logic($json, $config, function () {
                return [
                    [
                        'id' => 1,
                        'name' => 'Hello user_logic2',
                    ]
                ];
            });
        });
    }

    protected function _logic($json, $config, $callback)
    {
        $_data_option = $config['@option'] ?? [];
        $ruleType = $_data_option['_rule']['type'] ?? 1;
        $dataRule = $_data_option['_rule']['data_rule'] ?? 1;
        if ($ruleType == 1) {
            if ($dataRule == 1) {
                if (isset($_data_option['item'])) {
                    //return $_data_option;
                } else {
                    $_data_option['item'] = call_user_func($callback, $json, $config);
                }
                return $_data_option;
            } else if ($dataRule == 2) {
                // dd(isset($_data_option['item']),$_data_option, call_user_func($callback, $json, $config));

                if (isset($_data_option['item'])) {
                    $_data_option['item'] = array_merge($_data_option['item'], call_user_func($callback, $json, $config));
                } else {
                    $_data_option['item'] = call_user_func($callback, $json, $config);
                }
                return $_data_option;
            }
        } else if ($ruleType == 2) {
            if ($dataRule == 1) {
                if (isset($_data_option['data'])) {
                    //return $config['data'];
                } else {
                    $_data_option['data'] = call_user_func($callback, $json, $config);
                }
                return $_data_option;
            } else if ($dataRule == 2) {
                if (isset($_data_option['data'])) {
                    $_data_option['data'] = array_merge($_data_option['data'], call_user_func($callback, $json, $config));
                } else {
                    $_data_option['data'] = call_user_func($callback, $json, $config);
                }
                return $_data_option;
            }
        }
    }

    public function testReturnItem()
    {

        // 返回
        $data = [
            "item" => [
                "id" => 1,
                "name" => "Hello User",
            ]
        ];
        $this->assertEquals($this->json->getJson([
            "@source" => "user_logic1",
            "@option" => [
                "_rule" => [
                    "type" => 1, // type = 1 单个数据(item) 2 多个数据 (item)
                    "data_rule" => 1, // 1 不为空返回 2 追加
                ],
                "item" => [
                    "id" => 1,
                    "name" => "Hello User",
                ]
            ],
            "@structure" => [
                "item" => [
                    "@structure" => ":item",
                ]
            ]
        ]), $data);

        // 前面的数据不为空才返回
        $dataLogic = [
            "item" => [
                "id" => 1,
                "name" => "Hello user_logic1",
            ]
        ];
        $this->assertEquals($this->json->getJson([
            "@source" => "user_logic1",
            "@option" => [
                "_rule" => [
                    "type" => 1, // type = 1 单个数据(item) 2 多个数据 (item)
                    "data_rule" => 1 // 1 不为空返回 2 追加
                ],
            ],
            "@structure" => [
                "item" => [
                    "@structure" => ":item",
                ]
            ]
        ]), $dataLogic);

        // 合并
        $dataLogic = [
            "item" => [
                "id" => 1,
                "name" => "Hello user_logic1",
                "test" => "test"
            ]
        ];
        $this->assertEquals($this->json->getJson([
            "@source" => "user_logic1",
            "@option" => [
                "_rule" => [
                    "type" => 1, // type = 1 单个数据(item) 2 多个数据 (item)
                    "data_rule" => 2, // 1 不为空返回 2 追加
                ],
                "item" => [
                    "id" => 1,
                    "name" => "Hello User",
                    "test" => "test"
                ]
            ],
            "@structure" => [
                "item" => [
                    "@structure" => ":item",
                ]
            ]
        ]), $dataLogic);
    }

    public function testReturnData()
    {

        // 返回
        $data = [
            "data" => [
                [
                    "id" => 1,
                    "name" => "Hello User",
                ]

            ]
        ];
        $this->assertEquals($this->json->getJson([
            "@source" => "user_logic2",
            "@option" => [
                "_rule" => [
                    "type" => 2, // type = 1 单个数据(item) 2 多个数据 (item)
                    "data_rule" => 1, // 1 不为空返回 2 追加
                ],
                "data" => [
                    [
                        "id" => 1,
                        "name" => "Hello User",
                    ]

                ]
            ],
            "@structure" => [
                "data" => [
                    "@structure" => ":data",
                ]
            ]
        ]), $data);

        // 前面的数据不为空才返回
        $dataLogic = [
            "data" => [
                [
                    "id" => 1,
                    "name" => "Hello user_logic2",
                ]

            ]
        ];
        $this->assertEquals($this->json->getJson([
            "@source" => "user_logic2",
            "@option" => [
                "_rule" => [
                    "type" => 2, // type = 1 单个数据(item) 2 多个数据 (item)
                    "data_rule" => 1 // 1 不为空返回 2 追加
                ],
            ],
            "@structure" => [
                "data" => [
                    "@structure" => ":data",
                ]
            ]
        ]), $dataLogic);

        // 合并
        $dataLogic = [
            "data" => [

                [
                    "id" => 1,
                    "name" => "Hello User",
                ],
                [
                    "id" => 1,
                    "name" => "Hello user_logic2",
                ]

            ]
        ];
        $this->assertEquals($this->json->getJson([
            "@source" => "user_logic2",
            "@option" => [
                "_rule" => [
                    "type" => 2, // type = 1 单个数据(item) 2 多个数据 (item)
                    "data_rule" => 2, // 1 不为空返回 2 追加
                ],
                "data" => [
                    [
                        "id" => 1,
                        "name" => "Hello User",
                    ]
                ],
            ],
            "@structure" => [
                "data" => [
                    "@structure" => ":data",
                ]
            ],
        ]), $dataLogic);
    }
}
