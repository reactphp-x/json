<?php

namespace ReactphpX\Json;

class Json
{
    // 安全限制常量
    const MAX_RECURSION_DEPTH = 50;
    const MAX_ITERATIONS = 100;

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
            return $this->replaceParams($json, $current_context, [], 0);
        } else {
            return $this->replaceParams($json, $current_context ?: $this->data_sources, [], 0);
        }
    }

    protected function replaceParams($params, $current_context, $_data_option = [], $depth = 0)
    {
        // 递归深度限制
        if ($depth > self::MAX_RECURSION_DEPTH) {
            throw new SecurityException("Maximum recursion depth (" . self::MAX_RECURSION_DEPTH . ") exceeded");
        }

        // 二维数组递归
        if ($this->test->is_array($params)) {
            $params = array_map(fn ($param) => $this->replaceParams($param, $current_context, $_data_option, $depth + 1), $params);
        }
        // 是数据源，去解析他
        else if ($this->test->is_object($params)) {
            // 是数据源
            // {@source: 'xxxx'}
            if ($this->test->is_data_source($params, true)) {

                // 数据源有自己的上下文
                // {@context: 'xxxx'}
                if (isset($params['@context'])) {
                    $current_context = $this->replaceParams($params['@context'], $current_context, $_data_option, $depth + 1);
                }

                // 数据源的参数
                // {@option: ':id'}
                // {@option: 'xxxx'} // 会从data_options中取
                if (isset($params['@option'])) {
                    if (isset($params['@option']['_not_replace']) && $params['@option']['_not_replace']) {
                    } else {
                        $params['@option'] = $this->replaceParams($this->getDataOption($params, true), $current_context, $_data_option, $depth + 1);
                    }
                } else {
                    // 透传参数
                    $params['@option'] = $_data_option;
                }

                $params = $this->replaceParams($this->getDataStructure($params), $this->getDataSource($params, $current_context), $params['@option'], $depth + 1);
            }
            // 解析数据结构
            else if ($this->test->is_data_structure($params)) {

                if (isset($params['@context'])) {
                    $current_context = $this->replaceParams($params['@context'], $current_context, $_data_option, $depth + 1);
                }

                if (isset($params['@option'])) {
                    if (isset($params['@option']['_not_replace']) && $params['@option']['_not_replace']) {
                        
                    } else {
                        $params['@option'] = $this->replaceParams($this->getDataOption($params, true), $current_context, $_data_option, $depth + 1);
                    }
                } else {
                    $params['@option'] = $_data_option;
                }
                $params = $this->replaceParams($this->getDataStructure($params), $current_context, $params['@option'], $depth + 1);
            }
            // 解析上下文
            else if ($this->test->is_data_context($params)) {
                $params = $this->replaceParams($params['@context'], $current_context, $_data_option, $depth + 1);
            }
            // 替换数组
            else {

                $isSupportArray = $params['@is_array'] ?? false;
                unset($params['@is_array']);
                if ($isSupportArray && $this->test->is_array($current_context)) {
                    $params = array_map(fn ($param) => $this->replaceParams($params, $param, $_data_option, $depth + 1), $current_context);
                }
                else if (true || $this->test->is_object($current_context)) {
                    foreach ($params as $key => $value) {
                        $params[$key] = $this->replaceParams($value, $current_context, $_data_option, $depth + 1);
                    }
                }
            }

        }
        // 替换参数
        else if ($this->test->is_string($params)) {
            try {
                $params = $this->replaceParam($params, $current_context, $_data_option);
                // 安全：只允许执行注册的函数，并限制迭代次数
                if ($this->test->is_function($params)) {
                    // 验证是否为注册的数据源、数据结构或数据选项中的函数
                    if (!$this->isAllowedCallable($params)) {
                        throw new SecurityException("Unauthorized callable execution. Only registered functions are allowed.");
                    }
                    
                    $iterations = 0;
                    do {
                        if (++$iterations > self::MAX_ITERATIONS) {
                            throw new SecurityException("Maximum iteration limit (" . self::MAX_ITERATIONS . ") exceeded");
                        }
                        $params = call_user_func($params, $this);
                    } while ($this->test->is_function($params));
                    
                    if ($this->test->is_data_source($params)) {
                        $params = $this->getJson($params);
                    }
                }
            } 
            catch (SecurityException $e) {
                throw $e;
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
            // 安全：$obj 来自 current_context，可能是用户输入，禁止执行未注册的函数
            // 这里不执行函数，只返回空字符串，避免代码注入
            $obj = '';
        }

        if (strpos($param, ':') === 0 && substr_count($param, ':') === 1) {
            if (substr($param, 1) === '*') {
                return $obj;
            }
            $firstKey = explode('.', substr($param, 1))[0];
            if ($firstKey === '@option') {
                return data_get(['@option' => $_data_option], substr($param, 1));
            }
        
            return data_get($obj, substr($param, 1));
        }
        return preg_replace_callback("/:([\w\.]+)/", function ($match) use ($obj, $_data_option) {
            $key = $match[1];

            $firstKey = explode('.', $key)[0];

            if ($firstKey === '@option') {
                return data_get(['@option' => $_data_option], $key);
            }

            if (isset($obj[$firstKey]) && is_callable($obj[$firstKey])) {
                return '';
            }
            return data_get($obj, $key) ?: '';
        }, $param);
    }

    public function getDataSource($config, $current_context) {

        $_data_source = $config['@source'] ?? '';

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
                        // 安全：数据源函数已通过 registerDataSource 注册，允许执行，但限制迭代次数
                        $iterations = 0;
                        do {
                            if (++$iterations > self::MAX_ITERATIONS) {
                                throw new SecurityException("Maximum iteration limit (" . self::MAX_ITERATIONS . ") exceeded in data source");
                            }
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
                            '@source' => $item,
                        ], $current_context);
                    }
                    return $item;
                }, $_data_source);
            }
        }

        if ($_data_source === null) {
            // 安全：只记录关键标识，不记录完整配置
            $sourceKey = $config['@source'] ?? 'unknown';
            error_log("数据源未找到: " . (is_string($sourceKey) ? $sourceKey : 'invalid'), E_CORE_WARNING);
        }

        return $_data_source;
    }


    protected function getDataOption($params, $isObject = false)
    {
        if ($isObject || $this->test->is_object($params)) {
            
        } else {
            return ;
        }

        $_data_option = $params['@option'] ?? [];

        if (!$_data_option) {
            return $_data_option;
        }

        if ($this->test->is_string($_data_option)) {
            if (substr($_data_option, 0, 1) === ':') {
            } else {
                $_data_option = $this->data_options[$_data_option] ?? '';
                if ($_data_option) {
                    if ($this->test->is_function($_data_option)) {
                        // 安全：数据选项函数已通过 registerDataOption 注册，允许执行，但限制迭代次数
                        $iterations = 0;
                        do {
                            if (++$iterations > self::MAX_ITERATIONS) {
                                throw new SecurityException("Maximum iteration limit (" . self::MAX_ITERATIONS . ") exceeded in data option");
                            }
                            $_data_option = call_user_func($_data_option, $this, $params);
                        } while ($this->test->is_function($_data_option));
                    }
                }
            }
           
        } 

        if ($_data_option === null) {
            // 安全：只记录关键标识，不记录完整参数
            $optionKey = $params['@option'] ?? 'unknown';
            error_log("数据选项未找到: " . (is_string($optionKey) ? $optionKey : 'invalid'), E_CORE_WARNING);
        }
       
        return $_data_option;
    }

    protected function getDataStructure($config) {

        $_data_structure = $config['@structure'] ?? '';

        if (!$_data_structure) {
            return ':*';
        }
       
        if ($this->test->is_string($_data_structure)) {
            if (substr($_data_structure, 0, 1) == ':') {
            } else {
                $_data_structure = $this->data_structures[$_data_structure] ?? '';
                if ($_data_structure) {
                    if ($this->test->is_function($_data_structure)) {
                        // 安全：数据结构函数已通过 registerDataStructure 注册，允许执行，但限制迭代次数
                        $iterations = 0;
                        do {
                            if (++$iterations > self::MAX_ITERATIONS) {
                                throw new SecurityException("Maximum iteration limit (" . self::MAX_ITERATIONS . ") exceeded in data structure");
                            }
                            $_data_structure = call_user_func($_data_structure, $this, $config);
                        } while ($this->test->is_function($_data_structure));
                    }
                }
            }
           
        }

        if ($_data_structure === null) {
            // 安全：只记录关键标识，不记录完整配置
            $structureKey = $config['@structure'] ?? 'unknown';
            error_log("数据结构未找到: " . (is_string($structureKey) ? $structureKey : 'invalid'), E_CORE_WARNING);
        }

        return $_data_structure;

    }

    /**
     * 验证可调用对象是否被允许执行
     * 只允许通过 registerDataSource、registerDataStructure、registerDataOption 注册的函数
     * 
     * @param mixed $callable 要验证的可调用对象
     * @return bool
     */
    protected function isAllowedCallable($callable)
    {
        // 检查是否在注册的数据源中
        foreach ($this->data_sources as $source) {
            if ($source === $callable) {
                return true;
            }
        }
        
        // 检查是否在注册的数据结构中
        foreach ($this->data_structures as $structure) {
            if ($structure === $callable) {
                return true;
            }
        }
        
        // 检查是否在注册的数据选项中
        foreach ($this->data_options as $option) {
            if ($option === $callable) {
                return true;
            }
        }
        
        // 禁止执行系统函数
        if (is_string($callable)) {
            $dangerousFunctions = ['system', 'exec', 'shell_exec', 'passthru', 'proc_open', 
                                   'popen', 'eval', 'create_function', 'file_get_contents', 
                                   'file_put_contents', 'fopen', 'include', 'require', 
                                   'include_once', 'require_once'];
            if (in_array(strtolower($callable), $dangerousFunctions)) {
                return false;
            }
        }
        
        // 默认拒绝未注册的可调用对象
        return false;
    }

}