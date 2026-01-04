<?php 

namespace ReactphpX\Json;

class Test
{
    public function is_object($value)
    {
        if (is_array($value)) {
           return !array_is_list($value);
        }
        return false;
    }

    public function is_array($value)
    {
        if (is_array($value)) {
            return array_is_list($value);
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
            if (isset($value['@source']) && $value['@source']) {
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
            if (isset($value['@structure']) && $value['@structure']) {
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
            if (isset($value['@context']) && $value['@context']) {
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