<?php 

namespace Reactphp\Framework\Json;

class Test
{
    public function is_object($value)
    {
        if (is_array($value)) {
            $jsonObjectString = json_encode($value);
            if (strpos($jsonObjectString, "{") === 0) {
                return true;
            }
        }
        return false;
    }

    public function is_array($value)
    {
        if (is_array($value) && isset($value[0])) {
            return true;
        }
        return false;
    }

    public function is_string($value)
    {
        return is_string($value);
    }

    public function is_function($value)
    {
        return is_callable($value);
    }

    public function is_data_source($value, $isObject = false)
    {
        if ($isObject || $this->is_object($value)) {
            if (isset($value['_data_source']) && $value['_data_source']) {
                $_not_data_source = $value['_not_data_source'] ?? false;
                if ($_not_data_source === true) {
                    return false;
                }
                return true;
            }
        }

        return false;
    }

    public function is_data_structure($value, $isObject = false)
    {
        if ($isObject || $this->is_object($value)) {
            if (isset($value['_data_structure']) && $value['_data_structure']) {
                $_not_data_structure = $value['_not_data_structure'] ?? false;
                if ($_not_data_structure === true) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    public function is_data_context($value, $isObject = false)
    {
        if ($isObject || $this->is_object($value)) {
            if (isset($value['_data_context']) && $value['_data_context']) {
                $_not_data_context = $value['_not_data_context'] ?? false;
                if ($_not_data_context === true) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }
}