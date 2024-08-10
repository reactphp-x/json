<?php

namespace ReactphpX\Json;

class Json
{

    protected $data_sources = [];
    protected $data_structures = [];
    protected $data_options = [];

    protected $test;

    public function __construct()
    {
        $this->test = new Test();
    }

    public function registerDataSource($key, $value)
    {
        $this->data_sources[$key] = $value;
    }

    public function registerDataStructure($key, $value)
    {
        $this->data_structures[$key] = $value;
    }

    public function registerDataOption($key, $value)
    {
        $this->data_options[$key] = $value;
    }

    public function getJson($json, $current_context = [], $isForce = false)
    {
        if ($isForce) {
            return $this->replaceParams($json, $current_context);
        } else {
            return $this->replaceParams($json, $current_context ?: $this->data_sources);
        }
    }

    protected function replaceParams($params, $current_context, $_data_option = [])
    {

        // 二维数组递归
        if ($this->test->is_array($params)) {
            $params = array_map(fn ($param) => $this->replaceParams($param, $current_context), $params);
        }
        // 是数据源，去解析他
        else if ($this->test->is_object($params)) {
            // 是数据源
            // {_data_source: 'xxxx'}
            if ($this->test->is_data_source($params, true)) {

                // 数据源有自己的上下文
                // {_data_context: 'xxxx'}
                if (isset($params['_data_context'])) {
                    $current_context = $this->replaceParams($params['_data_context'], $current_context);
                }

                // 数据源的参数
                // {_data_option: ':id'}
                // {_data_option: 'xxxx'} // 会从data_options中取
                if (isset($params['_data_option'])) {
                    $params['_data_option'] = $this->replaceParams($this->getDataOption($params, true), $current_context, $_data_option);
                } else {
                    // 透传参数
                    $params['_data_option'] = $_data_option;
                }

                $params = $this->replaceParams($this->getDataStructure($params), $this->getDataSource($params, $current_context), $params['_data_option']);
            }
            // 解析数据结构
            else if ($this->test->is_data_structure($params)) {

                if (isset($params['_data_context'])) {
                    $current_context = $this->replaceParams($params['_data_context'], $current_context);
                }

                if (isset($params['_data_option'])) {
                    $params['_data_option'] = $this->replaceParams($this->getDataOption($params, true), $current_context, $_data_option);
                } else {
                    $params['_data_option'] = $_data_option;
                }
                $params = $this->replaceParams($this->getDataStructure($params), $current_context, $params['_data_option']);
            }
            // 解析上下文
            else if ($this->test->is_data_context($params)) {
                $params = $this->replaceParams($params['_data_context'], $current_context);
            }
            // 替换数组
            else {

                $isSupportArray = $params['_is_support_array'] ?? false;
                unset($params['_is_support_array']);
                if ($isSupportArray && $this->test->is_array($current_context)) {
                    $params = array_map(fn ($param) => $this->replaceParams($params, $param, $_data_option), $current_context);
                }
                else if (true || $this->test->is_object($current_context)) {
                    foreach ($params as $key => $value) {
                        $params[$key] = $this->replaceParams($value, $current_context, $_data_option);
                    }
                }
            }

        }
        // 替换参数
        else if ($this->test->is_string($params)) {
            try {
                $params = $this->replaceParam($params, $current_context, $_data_option);
                // todo 去掉
                if ($this->test->is_function($params)) {
                    do {
                        $params = call_user_func($params, $this);
                    } while ($this->test->is_function($params));
                    if ($this->test->is_data_source($params)) {
                        $params = $this->getJson($params);
                    }
                }
            } 
            catch (\Exception $e) {
                throw $e;
            }
            catch (\Throwable $th) {
                throw $th;
            }
            
        }

        return $params;

    }

    protected function replaceParam($param, $obj, $_data_option)
    {

        if (!$this->test->is_string($param)) {
            return $param;   
        }

        if (strpos($param, ':') < 0) {
            return $param;
        }

        if ($this->test->is_function($obj)) {
            $obj = call_user_func($obj, $this);
        }

        if (strpos($param, ':') === 0 && substr_count($param, ':') === 1) {
            if (substr($param, 1) === '*') {
                return $obj;
            }
            $firstKey = explode('.', substr($param, 1))[0];
            if ($firstKey === '_data_option') {
                return data_get(['_data_option' => $_data_option], substr($param, 1));
            }
        
            return data_get($obj, substr($param, 1));
        }
        return preg_replace_callback("/:([\w\.]+)/", function ($match) use ($obj, $_data_option) {
            $key = $match[1];

            $firstKey = explode('.', $key)[0];

            if ($firstKey === '_data_option') {
                return data_get(['_data_option' => $_data_option], $key);
            }

            if (isset($obj[$firstKey]) && is_callable($obj[$firstKey])) {
                return '';
            }
            return data_get($obj, $key) ?: '';
        }, $param);
    }

    public function getDataSource($config, $current_context) {

        $_data_source = $config['_data_source'] ?? '';

        if (!$_data_source) {
            return $_data_source;
        }

        $isCommon = false;

        if ($this->test->is_string($_data_source)) {

            if (substr($_data_source, 0, 1) === ':') {
                if (substr($_data_source, 1) === 'http_data') {
                } 
                $_data_source = $this->getJson($_data_source, $current_context, true);
            } else {
                $isCommon = true;
                $_data_source = $this->data_sources[$_data_source] ?? '';
                if ($_data_source) {
                    if ($this->test->is_function($_data_source)) {
                        do {
                            $_data_source = call_user_func($_data_source, $this, $config);
                        } while ($this->test->is_function($_data_source));
                    }
                }
               
            }
           
        } else {
            $isCommon = true;
        }

        if ($isCommon) {

            if ($this->test->is_object($_data_source)) {
                if ($this->test->is_data_source($_data_source, true)) {
                    $_data_source = $this->getJson($_data_source, $current_context, true);
                } 
                else if ($this->test->is_data_structure($_data_source, true)) {

                    $_data_source = $this->getJson($_data_source, $current_context, true);
                }
                else if ($this->test->is_data_context($_data_source, true)) {
                    $_data_source = $this->getJson($_data_source, $current_context, true);
                }
            }
            else if ($this->test->is_array($_data_source)) { 
                $_data_source = array_map(function ($item) use ($current_context) {
                    if (is_array($item)) {
                        return $this->getDataSource([
                            '_data_source' => $item,
                        ], $current_context);
                    }
                    return $item;
                }, $_data_source);
            }
        }

        if ($_data_source === null) {
            error_log("数据源未找到: ". json_encode($config), E_CORE_WARNING);
        }

        return $_data_source;
    }


    protected function getDataOption($params, $isObject = false)
    {
        if ($isObject || $this->test->is_object($params)) {
            
        } else {
            return ;
        }

        $_data_option = $params['_data_option'] ?? [];

        if (!$_data_option) {
            return $_data_option;
        }

        if ($this->test->is_string($_data_option)) {
            if (substr($_data_option, 0, 1) === ':') {
            } else {
                $_data_option = $this->data_options[$_data_option] ?? '';
                if ($_data_option) {
                    if ($this->test->is_function($_data_option)) {
                        do {
                            $_data_option = call_user_func($_data_option, $this, $params);
                        } while ($this->test->is_function($_data_option));
                    }
                }
            }
           
        } 

        if ($_data_option === null) {
            error_log("数据选项未找到: ". json_encode($params), E_CORE_WARNING);
        }
       
        return $_data_option;
    }

    protected function getDataStructure($config) {

        $_data_structure = $config['_data_structure'] ?? '';

        if (!$_data_structure) {
            return ':*';
        }
       
        if ($this->test->is_string($_data_structure)) {
            if (substr($_data_structure, 0, 1) == ':') {
            } else {
                $_data_structure = $this->data_structures[$_data_structure] ?? '';
                if ($_data_structure) {
                    if ($this->test->is_function($_data_structure)) {
                        do {
                            $_data_structure = call_user_func($_data_structure, $this, $config);
                        } while ($this->test->is_function($_data_structure));
                    }
                }
            }
           
        }

        if ($_data_structure === null) {
            // errorlog warning
            error_log("数据结构未找到: ". json_encode($config), E_CORE_WARNING);
        }

        return $_data_structure;

    }

}