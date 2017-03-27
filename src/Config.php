<?php

namespace Cargo;

/**
 * Class Config
 * @package Cargo
 */
class Config
{
    const PARAM_KEY_SEPARATOR = '.';

    protected static $params = [];

    /**
     * @param string $key
     * @param mixed  $default
     * @return string|array|int|bool
     */
    public static function getParam($key, $default)
    {
        $keyParts = explode('.', $key);
        $param = self::$params;
        $currentPropertyPath = [];
        foreach ($keyParts as $keyPart) {
            if (!isset($param[$keyPart])) {
                return $default;
            }
            $currentPropertyPath[] = $keyPart;
            $param = &$param[$keyPart];
        }

        return $param;
    }

    /**
     * @param array  $config
     * @param string $prefix
     * @return array
     */
    public static function addParamsFromArray(array $config, $prefix = '')
    {
        foreach ($config as $name => $configItem) {
            $paramName = $prefix.self::PARAM_KEY_SEPARATOR.$name;
            if (is_array($configItem)) {
                self::$params = array_merge(self::$params, self::addParamsFromArray($configItem, $paramName));
            } else {
                self::$params[$paramName] = $configItem;
            }
        }
    }
}
